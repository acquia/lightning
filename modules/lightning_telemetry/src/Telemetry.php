<?php

namespace Drupal\lightning_telemetry;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use GuzzleHttp\ClientInterface;

/**
 * Telemetry service.
 */
class Telemetry {

  const AMPLITUDE_API_URL = 'https://api.amplitude.com/httpapi';
  const AMPLITUDE_API_KEY = 'f32aacddde42ad34f5a3078a621f37a9';

  /**
   * The module handler to invoke the alter hook with.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

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
   * @param array $event
   *   The Amplitude event data.
   *
   * @return bool
   *   TRUE if the request to Amplitude was successful.
   */
  protected function sendEvent($event) {
    $response = $this->httpClient->request('POST', self::AMPLITUDE_API_URL, [
      'form_params' => [
        'api_key' => self::AMPLITUDE_API_KEY,
        'event' => json_encode($event),
      ]
    ]);
    $success = $response->getStatusCode() == 200;

    return $success;
  }

  /**
   * Creates and sends a cron event to Amplitude.
   */
  public function sendCronEvent() {
    $data = $this->gatherCronData();

    $cron_event = [
      'user_id' => $this->config_factory->get('system.site')->get('uuid'),
      'event_type' => 'Drupal cron ran',
      'event_properties' => $data,
    ];

    $this->sendEvent($cron_event);
  }

  /**
   * Gathers data for creating a cron event.
   *
   * @return array
   *   An array of event data.
   */
  protected function gatherCronData() {
    $installed_modules = $this->extensionListModule->getAllInstalledInfo();
    $lightning_modules_names = [
      'lightning_media',
      'lightning_workflow',
      'lightning_layout',
      'lightning_api',
    ];
    $installed_lightning_modules = array_intersect($lightning_modules_names, array_keys($installed_modules));

    $data = [
      'modules' => [
        'installed' => $installed_lightning_modules,
      ],
      'versions' => [
        'php' => phpversion(),
        'lightning' => $installed_modules['lightning']['version'],
        'drupal' => \Drupal::VERSION,
      ],
    ];

    return $data;
  }

}
