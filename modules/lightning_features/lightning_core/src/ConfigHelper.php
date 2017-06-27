<?php

namespace Drupal\lightning_core;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Config\InstallStorage;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\Extension;

/**
 * A facade to assist with manipulating default config.
 */
class ConfigHelper extends InstallStorage {

  /**
   * The extension whose default config is being manipulated by this object.
   *
   * @var \Drupal\Core\Extension\Extension
   */
  protected $extension;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ConfigHelper constructor.
   *
   * @param \Drupal\Core\Extension\Extension $extension
   *   The extension whose default config is being manipulated by this object.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(Extension $extension, ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct();
    $this->extension = $extension;
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Switches to the default config directory.
   *
   * @return $this
   *   The called object, for chaining.
   */
  public function install() {
    $this->directory = self::CONFIG_INSTALL_DIRECTORY;
    return $this;
  }

  /**
   * Switches to the optional config directory.
   *
   * @return $this
   *   The called object, for chaining.
   */
  public function optional() {
    $this->directory = self::CONFIG_OPTIONAL_DIRECTORY;
    return $this;
  }

  /**
   * Transparently loads a config entity from the extension's config.
   *
   * @param string $entity_type
   *   The entity type ID.
   * @param string $id
   *   The entity ID.
   * @param bool $force
   *   (optional) If TRUE, the entity is read from config even if it already
   *   exists. Defaults to FALSE.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The config entity, or NULL if it doesn't exist.
   */
  public function getEntity($entity_type, $id, $force = FALSE) {
    $storage = $this->entityTypeManager->getStorage($entity_type);
    $entity = $storage->load($id);

    if ($entity && empty($force)) {
      return $entity;
    }

    $prefixes = $this->getConfigPrefixes();

    return $storage->create(
      $this->read($prefixes[$entity_type] . '.' . $id)
    );
  }

  /**
   * Loads a simple config object from the extension's config.
   *
   * @param string $id
   *   The config object ID.
   *
   * @return \Drupal\Core\Config\Config
   *   The config object.
   */
  public function get($id) {
    $data = $this->read($id);
    return $this->configFactory->getEditable($id)->setData($data);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($id) {
    foreach ($this->getConfigPrefixes() as $entity_type => $prefix) {
      $prefix .= '.';

      if (Unicode::strpos($id, $prefix) === 0) {
        $entity = $this->getEntity(
          $entity_type,
          Unicode::substr($id, Unicode::strlen($prefix))
        );
        return $entity->delete();
      }
    }
    return $this->get($id)->delete();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll($prefix = '') {
    foreach ($this->listAll($prefix) as $id) {
      $this->delete($id);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getAllFolders() {
    return $this->getComponentNames([
      $this->extension->getName() => $this->extension,
    ]);
  }

  /**
   * Returns a map of config entity type IDs to config prefixes.
   *
   * @return string[]
   *   The config prefixes, keyed by the corresponding entity type ID.
   */
  protected function getConfigPrefixes() {
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

  /**
   * Creates a new ConfigHelper for a module.
   *
   * @param string $module
   *   The module name.
   *
   * @return static
   *   A new ConfigHelper object.
   */
  public static function forModule($module) {
    return new static(
      \Drupal::moduleHandler()->getModule($module),
      \Drupal::configFactory(),
      \Drupal::entityTypeManager()
    );
  }

  /**
   * Creates a new ConfigHelper for a theme.
   *
   * @param string $theme
   *   The theme name.
   *
   * @return static
   *   A new ConfigHelper object.
   */
  public static function forTheme($theme) {
    return new static(
      \Drupal::service('theme_handler')->getTheme($theme),
      \Drupal::configFactory(),
      \Drupal::entityTypeManager()
    );
  }

}
