<?php

namespace Acquia\Lightning;

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
    // ExtensionDiscovery still has a Doc Marten stuck in the dark ages, by
    // which I mean it calls procedural functions. So you pretty much can't use
    // it unless Drupal is bootstrapped to at least a basic install-time level.
    // That won't work for us, though, so we have to eval the missing functions
    // into existence. An ugly hack for sure, but DRUPAL CORE DROVE ME TO THIS.
    // And for good measure, let us only use file caching if Drupal is
    // bootstrapped.
    if (function_exists('drupal_valid_test_ua') && function_exists('drupal_get_profile')) {
      $file_cache = TRUE;
    }
    else {
      eval('function drupal_valid_test_ua() { return FALSE; }');
      eval('function drupal_get_profile() { return NULL; }');
      $file_cache = FALSE;
    }

    $this->discovery = new ExtensionDiscovery($app_root, $file_cache);
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
