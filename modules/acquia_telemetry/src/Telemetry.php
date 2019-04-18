<?php

namespace Drupal\acquia_telemetry;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\State\StateInterface;
use Drupal\lightning\ComponentDiscovery;
use GuzzleHttp\ClientInterface;

/**
 * Telemetry service.
 */
class Telemetry {

  /**
   * Amplitude API URL.
   *
   * @var string
   * @see https://developers.amplitude.com/#http-api
   */
  private $apiUrl = 'https://api.amplitude.com/httpapi';

  /**
   * Amplitude API key.
   *
   * This is not intended to be private. It is typically included in client
   * side code. Fetching data requires an additional API secret.
   *
   * @var string
   * @see https://developers.amplitude.com/#http-api
   */
  private $apiKey = 'f32aacddde42ad34f5a3078a621f37a9';

  /**
   * The extension.list.module service.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  private $moduleExtensionList;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  private $httpClient;

  /**
   * The config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  private $state;

  /**
   * The application root directory.
   *
   * @var string
   */
  private $root;

  /**
   * Constructs a telemetry object.
   *
   * @param \Drupal\Core\Extension\ModuleExtensionList $module_extension_list
   *   The extension.list.module service.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config.factory service.
   */
  public function __construct(ModuleExtensionList $module_extension_list, ClientInterface $http_client, ConfigFactoryInterface $config_factory, StateInterface $state, $app_root) {
    $this->moduleExtensionList = $module_extension_list;
    $this->httpClient = $http_client;
    $this->configFactory = $config_factory;
    $this->state = $state;
    $this->root = $app_root;
  }

  /**
   * Sends an event to Amplitude.
   *
   * @param array $event
   *   The Amplitude event.
   *
   * @return bool
   *   TRUE if the request to Amplitude was successful, FALSE otherwise.
   *
   * @see https://developers.amplitude.com/#http-api
   */
  private function sendEvent(array $event) {
    $response = $this->httpClient->request('POST', $this->apiUrl, [
      'form_params' => [
        'api_key' => $this->apiKey,
        'event' => Json::encode($event),
      ],
    ]);

    return $response->getStatusCode() === 200;
  }

  /**
   * Creates and sends an event to Amplitude.
   *
   * @param string $event_type
   *   The event type. This accepts any string that is not reserved. Reserved
   *   event types include: "[Amplitude] Start Session", "[Amplitude] End
   *   Session", "[Amplitude] Revenue", "[Amplitude] Revenue (Verified)",
   *   "[Amplitude] Revenue (Unverified)", and "[Amplitude] Merged User".
   * @param array $event_properties
   *   Event properties.
   *
   * @return bool
   *   TRUE if event was successfully sent, otherwise FALSE.
   *
   * @throws \Exception
   *   Thrown if state key acquia_telemetry.loud is TRUE and request fails.
   *
   * @see https://amplitude.zendesk.com/hc/en-us/articles/204771828#keys-for-the-event-argument
   */
  public function sendTelemetry($event_type, array $event_properties = []) {
    $event = $this->createEvent($event_type, $event_properties);

    // Failure to send Telemetry should never cause a user facing error or
    // interrupt a process. Telemetry failure should be graceful and quiet.
    try {
      return $this->sendEvent($event);
    }
    catch (\Exception $e) {
      if ($this->state->get('acquia_telemetry.loud')) {
        throw $e;
      }
      return FALSE;
    }
  }

  /**
   * Get an array of information about Lightning extensions.
   *
   * @return array
   *   An array of extension info keyed by the extensions machine name.
   */
  private function getExtensionInfo() {
    $all_modules = $this->moduleExtensionList->getAllAvailableInfo();
    $acquia_extensions = array_intersect_key($all_modules, array_flip($this->getAcquiaExtensionNames()));
    $extension_info = [];

    foreach ($acquia_extensions as $name => $extension) {
      // Version is unset for dev versions.
      $version = isset($extension['version']) ? $extension['version'] : $extension['core'];
      $extension_info[$name]['version'] = $version;
    }

    $installed_modules = $this->moduleExtensionList->getAllInstalledInfo();
    foreach ($acquia_extensions as $name => $extension) {
      $extension_info[$name]['status'] = array_key_exists($name, $installed_modules) ? 'enabled' : 'disabled';
    }

    return $extension_info;
  }

  /**
   * Creates an Amplitude event.
   *
   * @param string $type
   *   The event type.
   * @param array $properties
   *   The event properties.
   *
   * @return array
   *   An Amplitude event with basic info already populated.
   */
  private function createEvent($type, array $properties) {
    $default_properties = [
      'extensions' => $this->getExtensionInfo(),
      'php' => [
        'version' => phpversion(),
      ],
      'drupal' => [
        'version' => \Drupal::VERSION,
      ],
    ];

    return [
      'event_type' => $type,
      'user_id' => $this->getUserId(),
      'event_properties' => NestedArray::mergeDeep($default_properties, $properties),
    ];
  }

  /**
   * Gets a unique ID for this application. "User ID" is an Amplitude term.
   *
   * @return string
   *   Returns a hashed site uuid.
   */
  private function getUserId() {
    return Crypt::hashBase64($this->configFactory->get('system.site')->get('uuid'));
  }

  /**
   * Gets an array of all Acquia Drupal extensions.
   *
   * @return array
   *   A flat array of all Acquia Drupal extensions.
   */
  public function getAcquiaExtensionNames() {
    $discovery = new ComponentDiscovery($this->root);
    $acquia_extension_names = array_keys($discovery->getAll());

    return $acquia_extension_names;
  }

}
