<?php

namespace Drupal\lightning_dev;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Url;

class BehatConfigurator {

  protected $appRoot;

  protected $moduleHandler;

  public function __construct($app_root, ModuleHandlerInterface $module_handler) {
    $this->appRoot = $app_root;
    $this->moduleHandler = $module_handler;
  }

  protected function getUri() {
    return 'public://behat.yml';
  }

  protected function read() {
    $config = file_get_contents($this->getUri());
    return Yaml::decode($config);
  }

  protected function write(array $config) {
    file_put_contents($this->getUri(), Yaml::encode($config));
  }

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
