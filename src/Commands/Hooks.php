<?php

namespace Drupal\lightning\Commands;

use Drupal\Core\Plugin\CachedDiscoveryClearerInterface;
use Drush\Commands\DrushCommands;

/**
 * Implements Drush command hooks.
 */
class Hooks extends DrushCommands {

  /**
   * The plugin cache clearer service.
   *
   * @var \Drupal\Core\Plugin\CachedDiscoveryClearerInterface
   */
  protected $pluginCacheClearer;

  /**
   * Hooks constructor.
   *
   * @param \Drupal\Core\Plugin\CachedDiscoveryClearerInterface $plugin_cache_clearer
   *   The plugin cache clearer service.
   */
  public function __construct(CachedDiscoveryClearerInterface $plugin_cache_clearer) {
    $this->pluginCacheClearer = $plugin_cache_clearer;
  }

  /**
   * Clears the plugin discovery cache before updates run.
   *
   * @hook pre-command updatedb
   */
  public function onPreUpdate() {
    $this->pluginCacheClearer->clearCachedDefinitions();
  }

}
