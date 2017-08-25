<?php

namespace Drupal\lightning;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Executable\ExecutableInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\lightning\Annotation\Update;

/**
 * Plugin manager service for interactive updates.
 */
class UpdateManager extends DefaultPluginManager {

  /**
   * UpdateManager constructor.
   *
   * @param \Traversable $namespaces
   *   The current namespaces.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   The cache backend.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/Update',
      $namespaces,
      $module_handler,
      ExecutableInterface::class,
      Update::class
    );
    $this->factory = new UpdateFactory($this, $this->pluginInterface);
  }

  /**
   * {@inheritdoc}
   */
  protected function findDefinitions() {
    $definitions = parent::findDefinitions();
    ksort($definitions);

    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function getFactory() {
    return parent::getFactory();
  }

}
