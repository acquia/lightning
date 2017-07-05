<?php

namespace Drupal\lightning_inline_block;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\ctools_entity_mask\MaskContentEntityStorage;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InlineEntityStorage extends MaskContentEntityStorage {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

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
   */
  public function __construct($entity_type, $entity_manager, $cache, Connection $database) {
    parent::__construct($entity_type, $entity_manager, $cache);
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager'),
      $container->get('cache.entity'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function doLoadMultiple(array $ids = NULL) {
    $query = $this->database
      ->select('inline_entity', 'ie')
      ->fields('ie');

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
      $context = StorageContext::fromStorageRecord($record);

      $configuration = $context->getConfiguration();
      $blocks[$record->uuid] = unserialize($configuration['entity'])
        ->setStorageContext($context);
    }
    return $blocks;
  }

  /**
   * {@inheritdoc}
   */
  protected function doPostSave(EntityInterface $entity, $update) {
    /** @var \Drupal\lightning_inline_block\InlineEntityInterface $entity */
    parent::doPostSave($entity, $update);

    $entity->getStorageContext()->commit($entity);
  }

}
