<?php

namespace Drupal\lightning_core;

use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\InstallStorage;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Helps with reading and creating default configuration.
 */
class ConfigHelper {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * File storage reader for the current directory.
   *
   * @var FileStorage
   */
  protected $storage;

  /**
   * ConfigHelper constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ModuleHandlerInterface $module_handler, EntityTypeManagerInterface $entity_type_manager) {
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Switches to the default config directory for a module.
   *
   * @param string $module
   *   The module.
   *
   * @return $this
   *   The called object, for chaining.
   */
  public function install($module) {
    $dir = $this->moduleHandler->getModule($module)->getPath() . '/' . InstallStorage::CONFIG_INSTALL_DIRECTORY;
    $this->storage = new FileStorage($dir);
    return $this;
  }

  /**
   * Switches to the optional config directory for a module.
   *
   * @param string $module
   *   The module.
   *
   * @return $this
   *   The called object, for chaining.
   */
  public function optional($module) {
    $dir = $this->moduleHandler->getModule($module)->getPath() . '/' . InstallStorage::CONFIG_OPTIONAL_DIRECTORY;
    $this->storage = new FileStorage($dir);
    return $this;
  }

  /**
   * Reads a config file from the current directory.
   *
   * @param string $id
   *   The file to read, without the YML extension.
   *
   * @return mixed
   *   The values read from the file.
   */
  public function read($id) {
    return $this->storage->read($id);
  }

  /**
   * Creates a config entity from configuration in the current directory.
   *
   * If an entity of the specified type with the specified ID already exists,
   * nothing will happen.
   *
   * @param string $entity_type
   *   The config entity type ID.
   * @param string $id
   *   The unprefixed entity ID.
   *
   * @return $this
   *   The called object, for chaining.
   */
  public function createEntity($entity_type, $id) {
    $prefix = $this->entityTypeManager
      ->getDefinition($entity_type)
      ->getConfigPrefix();

    $storage = $this->entityTypeManager->getStorage($entity_type);

    $existing = $storage->load($id);
    if (empty($existing)) {
      $values = $this->read($prefix . '.' . $id);
      if ($values) {
        $storage->create($values)->save();
      }
    }
    return $this;
  }

}
