<?php

namespace Drupal\lightning_inline_block;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\ctools_entity_mask\MaskContentEntityStorage;
use Drupal\panelizer\PanelizerInterface;
use Drupal\user\SharedTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InlineEntityStorage extends MaskContentEntityStorage {

  use PanelizedEntityContextTrait;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The Panels IPE temp store.
   *
   * @var \Drupal\user\SharedTempStore
   */
  protected $tempStore;

  /**
   * The Panelizer service.
   *
   * @var \Drupal\panelizer\PanelizerInterface
   */
  protected $panelizer;

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
   * @param \Drupal\user\SharedTempStore $temp_store
   *   The Panels IPE temp store.
   * @param \Drupal\panelizer\PanelizerInterface $panelizer
   *   The Panelizer service.
   */
  public function __construct($entity_type, $entity_manager, $cache, Connection $database, SharedTempStore $temp_store, PanelizerInterface $panelizer) {
    parent::__construct($entity_type, $entity_manager, $cache);
    $this->database = $database;
    $this->tempStore = $temp_store;
    $this->panelizer = $panelizer;
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
      $container->get('user.shared_tempstore')->get('panels_ipe'),
      $container->get('panelizer')
    );
  }

  protected function getDisplay(EntityInterface $entity) {
    $display = $this->panelizer->getPanelsDisplay($entity, 'full');

    $this->ensureEntityContext($display, $entity);

    $configuration = $this->tempStore->get($display->getTempStoreId());
    if ($configuration) {
      $display->setConfiguration($configuration);
    }

    return $display;
  }

  /**
   * {@inheritdoc}
   */
  protected function doLoadMultiple(array $ids = NULL) {
    $query = $this->database->select('inline_entity', 'ie')->fields('ie');

    if ($ids) {
      $query->condition('uuid', $ids, 'IN');
    }

    return array_map(
      [$this, 'mapFromStorageRecord'],
      $query->execute()->fetchAllAssoc('uuid')
    );
  }

  protected function loadEntity($entity_type, $id) {
    $storage = $this->entityManager->getStorage($entity_type);

    $keys = $storage->getEntityType()->getKeys();

    if ($keys['revision']) {
      $revisions = $storage
        ->getQuery()
        ->allRevisions()
        ->condition($keys['id'], $id)
        ->sort($keys['revision'], 'DESC')
        ->range(0, 1)
        ->execute();

      $revision_id = key($revisions);
    }

    return isset($revision_id)
      ? $storage->loadRevision($revision_id)
      : $storage->load($id);
  }

  protected function mapFromStorageRecord(\stdClass $record) {
    $entity = $this->loadEntity($record->entity_type, $record->entity_id);

    $configuration = $this->getDisplay($entity)->getConfiguration();

    return unserialize($configuration['blocks'][$record->block_id]['entity']);
  }

  /**
   * {@inheritdoc}
   */
  protected function doPostSave(EntityInterface $entity, $update) {
    parent::doPostSave($entity, $update);

    if ($update) {
      $record = $this->database
        ->select('inline_entity', 'ie')
        ->fields('ie')
        ->condition('uuid', $entity->uuid())
        ->execute()
        ->fetch();

      $host = $this->loadEntity($record->entity_type, $record->entity_id);

      $display = $this->getDisplay($host);
      $configuration = $display->getBlock($record->block_id)->getConfiguration();
      $configuration['entity'] = serialize($entity);
      $display->updateBlock($record->block_id, $configuration);
      $this->tempStore->set($display->getTempStoreId(), $display->getConfiguration());
    }
  }

}
