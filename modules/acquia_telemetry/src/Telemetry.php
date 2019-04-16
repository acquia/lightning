<?php

namespace Drupal\acquia_telemetry;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use GuzzleHttp\ClientInterface;

/**
 * Telemetry service.
 */
class Telemetry {

  /**
   * Amplitude API URL.
   *
   * @see https://developers.amplitude.com/#http-api
   */
  const AMPLITUDE_API_URL = 'https://api.amplitude.com/httpapi';

  /**
   * Amplitude API key.
   *
   * This is not intended to be private. It is typically included in client
   * side code. Fetching data requires an additional API secret.
   *
   * @see https://developers.amplitude.com/#http-api
   */
  const AMPLITUDE_API_KEY = 'f32aacddde42ad34f5a3078a621f37a9';

  /**
   * The extension.list.module service.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $extensionListModule;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The config.factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config_factory;

  /**
   * Constructs a telemetry object.
   *
   * @param \Drupal\Core\Extension\ModuleExtensionList $extension_list_module
   *   The extension.list.module service.
   * @param \GuzzleHttp\ClientInterface $http_client
   *   The HTTP client.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config.factory service.
   */
  public function __construct(ModuleExtensionList $extension_list_module, ClientInterface $http_client, ConfigFactoryInterface $config_factory) {
    $this->extensionListModule = $extension_list_module;
    $this->httpClient = $http_client;
    $this->config_factory = $config_factory;
  }

  /**
   * Sends an event to Amplitude.
   *
   * @param \Drupal\acquia_telemetry\Event $event
   *   The Amplitude event.
   *
   * @return bool
   *   TRUE if the request to Amplitude was successful, FALSE otherwise.
   *
   * @see https://developers.amplitude.com/#http-api
   */
  protected function sendEvent(Event $event) {
    $response = $this->httpClient->request('POST', self::AMPLITUDE_API_URL, [
      'form_params' => [
        'api_key' => self::AMPLITUDE_API_KEY,
        'event' => Json::encode($event),
      ]
    ]);

    return $response->getStatusCode() == 200;
  }

  /**
   * Creates and sends an event to Amplitude.
   *
   * @param string $event_type
   *   The event type. This accepts any string that is not reserved. Reserved
   *   event types include: "[Amplitude] Start Session", "[Amplitude] End
   *   Session", "[Amplitude] Revenue", "[Amplitude] Revenue (Verified)",
   *   "[Amplitude] Revenue (Unverified)", and "[Amplitude] Merged User".
   *
   * @param array $event_properties
   *   Event properties.
   *
   * @throws \Exception
   *
   * @see https://amplitude.zendesk.com/hc/en-us/articles/204771828#keys-for-the-event-argument
   */
  public function sendTelemetry($event_type, array $event_properties = []) {
    $event = $this->createEvent($event_type, $event_properties);

    // Failure to send Telemetry should never cause a user facing error or
    // interrupt a process. Telemetry failure should be graceful and quiet.
    try {
      $this->sendEvent($event);
    }
    catch (\Exception $e) {
      if (getenv('ACQUIA_TELEMETRY_LOUD')) {
        throw $e;
      }
    }
  }

  /**
   * Get an array of information about Lightning extensions.
   *
   * @return array
   *   An array of extension info keyed by the extensions machine name.
   */
  protected function getExtensionInfo() {
    $all_modules = $this->extensionListModule->getAllAvailableInfo();
    $acquia_extensions = array_intersect_key($all_modules, array_flip($this->getAcquiaExtensionNames()));
    $extension_info = [];

    foreach ($acquia_extensions as $name => $extension) {
      // Version is unset for dev versions.
      $version = $extension['version'] ? $extension['version'] : $extension['core'];
      $extension_info[$name]['version'] = $version;
    }

    $installed_modules = $this->extensionListModule->getAllInstalledInfo();
    foreach ($acquia_extensions as $name => $extension) {
      if (array_key_exists($name, $installed_modules)) {
        $extension_info[$name]['status'] = 'enabled';
      }
      else {
        $extension_info[$name]['status']  = 'disabled';
      }
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
   * @return \Drupal\acquia_telemetry\Event
   *   An Amplitude event with basic info already populated.
   */
  protected function createEvent($type, $properties) {
    $user_id = $this->getUserId();
    $default_properties['extensions'] = $this->getExtensionInfo();
    $default_properties['php']['version'] = phpversion();
    $default_properties['drupal']['version'] = \Drupal::VERSION;
    $properties = NestedArray::mergeDeep($default_properties, $properties);
    $event = new Event($type, $user_id, $properties);

    return $event;
  }

  /**
   * Gets a unique ID for this application. "User ID" is an Amplitude term.
   *
   * @return string
   */
  protected function getUserId() {
    return Crypt::hashBase64($this->config_factory->get('system.site')->get('uuid'));
  }

  /**
   * Gets an array of all Acquia Drupal extensions.
   *
   * @return array
   *   A flat array of all Acquia Drupal extensions.
   */
  public function getAcquiaExtensionNames(): array {
    $acquia_extension_names = [
      'lightning',
      'lightning_api',
      'lightning_core',
      'lightning_dev',
      'lightning_layout',
      'lightning_media',
      'lightning_workflow',
    ];

    return $acquia_extension_names;
  }

}
