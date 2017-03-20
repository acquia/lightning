<?php

namespace Drupal\lightning_core;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
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
    $prefixes = $this->getConfigPrefixMap();

    $storage = $this->entityTypeManager->getStorage($entity_type);

    $existing = $storage->load($id);
    if (empty($existing)) {
      $values = $this->read($prefixes[$entity_type] . '.' . $id);
      if ($values) {
        $storage->create($values)->save();
      }
    }
    return $this;
  }

  /**
   * Deletes an entity created from default configuration.
   *
   * @param string $id
   *   The configuration ID.
   */
  public function delete($id) {
    // Get the entity type prefix map and filter it by the ID to determine what
    // entity type this ID represents.
    $prefixes = array_filter($this->getConfigPrefixMap(), function ($prefix) use ($id) {
      return strpos($id, $prefix . '.') === 0;
    });

    $entity_type = key($prefixes);
    if ($entity_type) {
      // Strip the prefix off the ID.
      $id = substr($id, strlen(current($prefixes)) + 1);

      $entity = $this->entityTypeManager->getStorage($entity_type)->load($id);
      if ($entity) {
        $entity->delete();
      }
    }
  }

  /**
   * Deletes all entities created from default configuration.
   */
  public function deleteAll() {
    $all = $this->storage->listAll();
    array_walk($all, [$this, 'delete']);
  }

  /**
   * Returns a map of config entity types to config prefixes.
   *
   * @return string[]
   *   The config prefixes, keyed by their corresponding entity type ID.
   */
  protected function getConfigPrefixMap() {
    $prefix_map = [];

    foreach ($this->entityTypeManager->getDefinitions() as $id => $definition) {
      if ($definition instanceof ConfigEntityTypeInterface) {
        $prefix_map[$id] = $definition->getConfigPrefix();
      }
    }

    return $prefix_map;
  }

  /**
   * Checks if a config entity is bundled with Lightning.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   The config entity.
   *
   * @return bool
   *   Whether the config entity is marked as being bundled with Lightning.
   */
  public static function isBundled(ConfigEntityInterface $entity) {
    return (bool) $entity->getThirdPartySetting('lightning', 'bundled', FALSE);
  }

}
