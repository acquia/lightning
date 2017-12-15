<?php

namespace Drupal\lightning;

use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ExtensionDiscovery;

/**
 * Helper object to locate Lightning components and sub-components.
 */
class ComponentDiscovery {

  /**
   * Prefix that Lightning components are expected to start with.
   */
  const COMPONENT_PREFIX = 'lightning_';

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
   * Returns extension objects for all Lightning components.
   *
   * @return Extension[]
   *   Array of extension objects for all Lightning components.
   */
  public function getAll() {
    if (is_null($this->components)) {
      $identifier = self::COMPONENT_PREFIX;

      $filter = function (Extension $module) use ($identifier) {
        return strpos($module->getName(), $identifier) === 0;
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
    $identifier = self::COMPONENT_PREFIX;

    $filter = function (Extension $module) use ($identifier) {
      // Assumes that:
      // 1. Lightning sub-components are always in a sub-directory within the
      //    main component.
      // 2. The main component's directory starts with "lightning_".
      // E.g.: "/lightning_core/modules/lightning_search".
      $path = explode(DIRECTORY_SEPARATOR, $module->getPath());
      $parent = $path[count($path)-3];
      return strpos($parent, $identifier) !== 0;
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
    return array_diff_key($this->getAll(), $this->getMainComponents());
  }

}
