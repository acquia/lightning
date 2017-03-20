<?php

namespace Drupal\lightning_dev;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Url;

/**
 * Generates centralized Behat configuration for modules that support it.
 */
class BehatConfigurator {

  /**
   * The Drupal application root.
   *
   * @var string
   */
  protected $appRoot;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * BehatConfigurator constructor.
   *
   * @param string $app_root
   *   The Drupal application root.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct($app_root, ModuleHandlerInterface $module_handler) {
    $this->appRoot = $app_root;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Returns the URI of the Behat configuration file.
   *
   * @return string
   *   The URI of the Behat configuration file.
   */
  protected function getUri() {
    return 'public://behat.yml';
  }

  /**
   * Reads the Behat configuration file.
   *
   * @return array
   *   The parsed Behat configuration.
   */
  protected function read() {
    $config = file_get_contents($this->getUri());
    return Yaml::decode($config);
  }

  /**
   * Writes the Behat configuration to disk.
   *
   * @param array $config
   *   The Behat configuration.
   */
  protected function write(array $config) {
    file_put_contents($this->getUri(), Yaml::encode($config));
  }

  /**
   * Regenerates the base configuration.
   *
   * @param string $base_url
   *   (optional) The base URL of the Drupal site, for the Mink Extension.
   */
  public function generate($base_url = NULL) {
    $profile = [];

    // Use the Mink Extension, if available.
    if (class_exists('\Behat\MinkExtension\ServiceContainer\MinkExtension')) {
      if (empty($base_url)) {
        $base_url = Url::fromUri('internal:/')->setAbsolute();
      }
      $profile['extensions']['Behat\MinkExtension'] = [
        'base_url' => (string) $base_url,
        'goutte' => NULL,
        'selenium2' => [
          'wd_host' => 'http://127.0.0.1:4444/wd/hub',
          'browser' => 'chrome',
        ],
      ];
    }

    // Use the Drupal Extension, if available.
    if (class_exists('\Drupal\DrupalExtension\ServiceContainer\DrupalExtension')) {
      $profile['extensions']['Drupal\DrupalExtension'] = [
        'api_driver' => 'drupal',
        'blackbox' => NULL,
        'drupal' => [
          'drupal_root' => (string) $this->appRoot,
        ],
        'drush' => [
          'alias' => 'self',
        ],
        'subcontexts' => [
          'autoload' => FALSE,
        ],
        'selectors' => [
          'error_message_selector' => '.messages [role="alert"]',
          'login_form_selector' => '#user-login-form',
        ],
      ];
    }

    $this->write(['default' => $profile]);
  }

  /**
   * Adds a module's Behat configuration to the central configuration file.
   *
   * @param string|\Drupal\Core\Extension\Extension $module
   *   The module to add.
   */
  public function add($module) {
    if (is_string($module)) {
      $module = $this->moduleHandler->getModule($module);
    }
    $base_path = $this->appRoot . '/' . $module->getPath();

    $config = $this->read();

    $import = $base_path . '/tests/behat.yml';
    if (file_exists($import)) {
      $import = file_get_contents($import);
      $import = str_replace('%paths.base%', $base_path, $import);
      $import = Yaml::decode($import);

      $config = array_merge_recursive($config, $import);
    }
    $this->write($config);
  }

}
