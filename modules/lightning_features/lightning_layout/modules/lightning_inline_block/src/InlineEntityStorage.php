<?php

namespace Drupal\lightning_inline_block;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\ctools_entity_mask\MaskContentEntityStorage;
use Drupal\panels\Storage\PanelsStorageManagerInterface;
use Drupal\user\SharedTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InlineEntityStorage extends MaskContentEntityStorage {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The Panels storage manager.
   *
   * @var \Drupal\panels\Storage\PanelsStorageManagerInterface
   */
  protected $panelsStorage;

  /**
   * The Panels IPE temp store.
   *
   * @var \Drupal\user\SharedTempStore
   */
  protected $tempStore;

  /**
   * InlineBlockContentStorage constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend to be used.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\panels\Storage\PanelsStorageManagerInterface $panels_storage
   *   The Panels storage manager.
   * @param \Drupal\user\SharedTempStore $temp_store
   *   The Panels IPE temp store.
   */
  public function __construct($entity_type, $entity_manager, $cache, Connection $database, PanelsStorageManagerInterface $panels_storage, SharedTempStore $temp_store) {
    parent::__construct($entity_type, $entity_manager, $cache);
    $this->database = $database;
    $this->panelsStorage = $panels_storage;
    $this->tempStore = $temp_store;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager'),
      $container->get('cache.entity'),
      $container->get('database'),
      $container->get('panels.storage_manager'),
      $container->get('user.shared_tempstore')->get('panels_ipe')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function doLoadMultiple(array $ids = NULL) {
    $query = $this->database->select('inline_entity', 'ie')->fields('ie');

    if ($ids) {
      $query->condition('uuid', $ids, 'IN');
    }
    return $this->mapFromStorageRecords($query->execute()->fetchAll());
  }

  /**
   * {@inheritdoc}
   */
  protected function mapFromStorageRecords(array $records) {
    $blocks = [];

    foreach ($records as $record) {
      $display = $this->panelsStorage->load($record->storage_type, $record->storage_id);

      if ($record->temp_store_id) {
        $configuration = $this->tempStore->get($record->temp_store_id);
        if ($configuration) {
          $display->setConfiguration($configuration);
        }
      }

      $configuration = $display->getConfiguration();
      $configuration = $configuration['blocks'][$record->block_id];
      $blocks[$record->uuid] = unserialize($configuration['entity'])
        ->setDisplay($display, $record->temp_store_id)
        ->setConfiguration($configuration);
    }

    return $blocks;
  }

  /**
   * {@inheritdoc}
   */
  protected function doPostSave(EntityInterface $entity, $update) {
    /** @var \Drupal\lightning_inline_block\InlineEntityInterface $entity */
    parent::doPostSave($entity, $update);

    $display = $entity->getDisplay();

    // Ensure that the block configuration has at least a region and plugin ID.
    $configuration = $entity->getConfiguration();
    if (empty($configuration['id'])) {
      $configuration['id'] = 'inline_entity:' . $entity->getEntityTypeId() . ':' . $entity->bundle();
    }
    if (empty($configuration['region'])) {
      $regions = $display->getRegionNames();
      $configuration['region'] = key($regions);
    }
    $configuration['entity'] = serialize($entity);

    if (isset($configuration['uuid'])) {
      $display->updateBlock($configuration['uuid'], $configuration);
    }
    else {
      $configuration['uuid'] = $display->addBlock($configuration);
      $entity->setConfiguration($configuration);
    }

    $temp_store_id = $entity->getTempStoreId();
    $this->database
      ->merge('inline_entity')
      ->key('uuid', $entity->uuid())
      ->fields([
        'storage_type' => $display->getStorageType(),
        'storage_id' => $display->getStorageId(),
        'temp_store_id' => $temp_store_id,
        'block_id' => $configuration['uuid'],
      ])
      ->execute();

    $this->tempStore->set($temp_store_id, $display->getConfiguration());
  }

}
