<?php

namespace Drupal\lightning;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ExtensionDiscovery;

/**
 * Helper object to locate Lightning components and sub-components.
 */
class ComponentDiscovery {

  /**
   * The extension discovery iterator.
   *
   * @var \Drupal\Core\Extension\ExtensionDiscovery
   */
  protected $discovery;

  /**
   * The Lightning profile extension object.
   *
   * @var Extension
   */
  protected $profile;

  /**
   * Cache of all discovered components.
   *
   * @var Extension[]
   */
  protected $components;

  /**
   * ComponentDiscovery constructor.
   *
   * @param string $app_root
   *   The application root directory.
   */
  public function __construct($app_root) {
    $this->discovery = new ExtensionDiscovery($app_root);
  }

  /**
   * Returns an extension object for the Lightning profile.
   *
   * @return \Drupal\Core\Extension\Extension
   *   The Lightning profile extension object.
   *
   * @throws \RuntimeException
   *   If the Lightning profile is not found in the system.
   */
  protected function getProfile() {
    if (empty($this->profile)) {
      $profiles = $this->discovery->scan('profile');

      if (empty($profiles['lightning'])) {
        throw new \RuntimeException('Lightning profile not found.');
      }
      $this->profile = $profiles['lightning'];
    }
    return $this->profile;
  }

  /**
   * Returns the base path for all Lightning components.
   *
   * @return string
   *   The base path for all Lightning components.
   */
  protected function getBaseComponentPath() {
    return $this->getProfile()->getPath() . '/modules/lightning_features';
  }

  /**
   * Returns extension objects for all Lightning components.
   *
   * @return Extension[]
   *   Array of extension objects for all Lightning components.
   */
  public function getAll() {
    if (is_null($this->components)) {
      $base_path = $this->getBaseComponentPath();

      $filter = function (Extension $module) use ($base_path) {
        return strpos($module->getPath(), $base_path) === 0;
      };

      $this->components = array_filter($this->discovery->scan('module'), $filter);
    }
    return $this->components;
  }

  /**
   * Returns extension objects for all main Lightning components.
   *
   * @return Extension[]
   *   Array of extension objects for top-level Lightning components.
   */
  public function getMainComponents() {
    $base_path = $this->getBaseComponentPath();

    $filter = function (Extension $module) use ($base_path) {
      return dirname($module->getPath()) == $base_path;
    };

    return array_filter($this->getAll(), $filter);
  }

  /**
   * Returns extension object for all Lightning sub-components.
   *
   * @return Extension[]
   *   Array of extension objects for Lightning sub-components.
   */
  public function getSubComponents() {
    $base_path = $this->getBaseComponentPath();

    $filter = function (Extension $module) use ($base_path) {
      return strlen(dirname($module->getPath())) > strlen($base_path);
    };

    return array_filter($this->getAll(), $filter);
  }

}
