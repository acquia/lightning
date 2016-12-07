<?php

namespace Drupal\lightning_core;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\InstallStorage;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides helpful methods for dealing with configuration.
 */
class ConfigHelper {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Cached FileStorage objects, keyed by module.
   *
   * @var FileStorage[]
   */
  protected $storage = [];

  /**
   * ConfigHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * Creates a config entity from default configuration.
   *
   * @param string $entity_type
   *   The config entity type ID.
   * @param string $id
   *   The unprefixed entity ID.
   * @param string $module
   *   (optional) The module which has the default configuration.
   */
  public function create($entity_type, $id, $module = 'lightning_core') {
    $id = $this->entityTypeManager->getDefinition($entity_type)->getConfigPrefix() . '.' . $id;

    $values = $this->read($id, $module);
    if ($values) {
      $this->entityTypeManager->getStorage($entity_type)->create($values)->save();
    }
  }

  /**
   * Reads a stored config file from a module's config/install directory.
   *
   * @param string $id
   *   The config ID.
   * @param string $module
   *   (optional) The module to search. Defaults to 'lightning_core'.
   *
   * @return array
   *   The config data.
   */
  public function read($id, $module = 'lightning_core') {
    if (empty($this->storage[$module])) {
      $dir = sprintf(
        '%s/%s',
        $this->moduleHandler->getModule($module)->getPath(),
        InstallStorage::CONFIG_INSTALL_DIRECTORY
      );
      $this->storage[$module] = new FileStorage($dir);
    }
    return $this->storage[$module]->read($id);
  }

}
