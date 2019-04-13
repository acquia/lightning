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
   * @param array $event
   */
  protected function sendEvent($event) {
    // Project ID: 221942
    $post_data = [
      'api_key' => 'f32aacddde42ad34f5a3078a621f37a9',
      'event' => json_encode($event),
    ];

    $this->httpClient->request('POST', self::AMPLITUDE_API_URL, [
      'form_params' => $post_data,
    ]);
  }

  /**
   *
   */
  public function sendCronEvent() {
    $data = $this->gatherCronData();

    $event = [
      'user_id' => $this->config_factory->get('system.site.uuid'),
      'event_type' => 'cron',
      'event_properties' => $data,
    ];

    $this->sendEvent($event);
  }

  /**
   * @return array
   */
  protected function gatherCronData() {
    $installed_modules = $this->extensionListModule->getAllInstalledInfo();
    $lightning_modules_names = [
      'lightning_media',
      'lightning_workflow',
      'lightning_layout',
      'lightning_api',
    ];
    $installed_lightning_modules = array_intersect_key($lightning_modules_names, $installed_modules);

    $data = [
      'modules' => [
        'installed' => $installed_lightning_modules,
      ],
    ];

    return $data;
  }

}
