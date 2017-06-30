<?php

namespace Drupal\lightning_inline_block;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\ctools_entity_mask\MaskContentEntityStorage;
use Drupal\lightning_inline_block\Entity\InlineBlockContent;
use Drupal\panels\Storage\PanelsStorageManagerInterface;
use Drupal\user\SharedTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InlineBlockContentStorage extends MaskContentEntityStorage {

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
    $query = $this->database->select('inline_block', 'ib')->fields('ib');

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

      if ($record->temp_store_key) {
        $configuration = $this->tempStore->get($record->temp_store_key);
        if ($configuration) {
          $display->setConfiguration($configuration);
        }
      }

      /** @var InlineBlockContent $block */
      $block = $display->getBlock($record->block_id)->getEntity();
      $block->display = $display;
      $block->blockId = $record->block_id;
      $block->tempStoreKey = $record->temp_store_key;
      $blocks[$record->uuid] = $block;
    }

    return $blocks;
  }

  /**
   * {@inheritdoc}
   */
  protected function doPostSave(EntityInterface $entity, $update) {
    /** @var \Drupal\lightning_inline_block\Entity\InlineBlockContent $entity */
    parent::doPostSave($entity, $update);

    $update ? $this->updateBlock($entity) : $this->insertBlock($entity);
  }

  /**
   * Reacts when an inline block is 'inserted'.
   *
   * @param \Drupal\lightning_inline_block\Entity\InlineBlockContent $block
   *   The inline block entity.
   */
  protected function insertBlock(InlineBlockContent $block) {
    // We expect $block->display to be set because inline blocks cannot be saved
    // without a Panels display. If it's not set, this will fatal. Good.
    $display = $block->display;

    // List the regions in the layout so that we can choose a default region if
    // the block doesn't specify one.
    $regions = $display->getRegionNames();

    $block->blockId = $display->addBlock([
      'id' => 'inline_entity',
      'region' => $block->region ?: key($regions),
      'entity' => serialize($block),
    ]);

    $temp_store_key = $display->getTempStoreId();
    $this->database
      ->insert('inline_block')
      ->fields([
        'uuid' => $block->uuid(),
        'storage_type' => $display->getStorageType(),
        'storage_id' => $display->getStorageId(),
        'temp_store_key' => $temp_store_key,
        'block_id' => $block->blockId,
      ])
      ->execute();

    $this->tempStore->set($temp_store_key, $display->getConfiguration());
  }

  /**
   * Reacts when an inline block is 'updated'.
   *
   * @param \Drupal\lightning_inline_block\Entity\InlineBlockContent $block
   *   The inline block entity.
   */
  protected function updateBlock(InlineBlockContent $block) {
    $display = $block->display;

    $configuration = $display->getBlock($block->blockId)->getConfiguration();
    $configuration['entity'] = serialize($block);
    $display->updateBlock($block->blockId, $configuration);

    if ($block->tempStoreKey) {
      $this->tempStore->set($block->tempStoreKey, $display->getConfiguration());
    }
    else {
      $this->panelsStorage->save($display);
    }
  }

}
