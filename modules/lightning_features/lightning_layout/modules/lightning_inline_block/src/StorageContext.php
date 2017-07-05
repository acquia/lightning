<?php

namespace Drupal\lightning_inline_block;

use Drupal\Core\Entity\EntityInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

class StorageContext {

  /**
   * @var \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant
   */
  protected $display;

  protected $storageType;

  protected $storageId;

  protected $tempStoreId;

  protected $configuration = [];

  public function __construct($storage_type, $storage_id, $temp_store_id = NULL, $block_id = NULL) {
    $this->storageType = $storage_type;
    $this->storageId = $storage_id;
    $this->tempStoreId = $temp_store_id;

    if ($block_id) {
      $configuration = $this->getDisplay()->getConfiguration();

      if (isset($configuration['blocks'][$block_id])) {
        $this->setConfiguration($configuration['blocks'][$block_id]);
      }
    }
  }

  public static function fromPanelsDisplay(PanelsDisplayVariant $display) {
    return new static(
      $display->getStorageType(),
      $display->getStorageId()
    );
  }

  public static function fromUuid($uuid) {
    $record = \Drupal::database()
      ->select('inline_entity', 'ie')
      ->fields('ie')
      ->condition('uuid', $uuid)
      ->execute()
      ->fetch();

    return static::fromStorageRecord($record);
  }

  public static function fromEntity(InlineEntityInterface $entity) {
    $context = static::fromUuid($entity->uuid());

    $entity->setStorageContext($context);

    return $context;
  }

  public static function fromStorageRecord(\stdClass $record) {
    return new static(
      $record->storage_type,
      $record->storage_id,
      $record->temp_store_id,
      $record->block_id
    );
  }

  public function getDisplay() {
    if (empty($this->display)) {
      $display = $this->panelsStorage()->load($this->storageType, $this->storageId);

      if (empty($this->tempStoreId)) {
        $this->tempStoreId = $display->getTempStoreId();
      }

      $configuration = $this->tempStore()->get($this->getTempStoreId());
      if ($configuration) {
        $display->setConfiguration($configuration);
      }

      $this->display = $display;
    }
    return $this->display;
  }

  public function getConfiguration() {
    return $this->configuration;
  }

  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  public function commit(EntityInterface $entity) {
    $display = $this->getDisplay();

    $configuration = $this->getConfiguration();

    if (empty($configuration['region'])) {
      $regions = $display->getRegionNames();
      $configuration['region'] = key($regions);
    }
    // The entity will always use the inline_entity block plugin.
    $configuration['id'] = 'inline_entity';
    $configuration['entity'] = serialize($entity);

    if (isset($configuration['uuid'])) {
      $display->updateBlock($configuration['uuid'], $configuration);
    }
    else {
      $configuration['uuid'] = $display->addBlock($configuration);
    }
    $this->setConfiguration($configuration);

    $this->database()
      ->merge('inline_entity')
      ->key('uuid', $entity->uuid())
      ->fields([
        'storage_type' => $this->storageType,
        'storage_id' => $this->storageId,
        'temp_store_id' => $this->tempStoreId,
        'block_id' => $configuration['uuid'],
      ])
      ->execute();

    $this->tempStore()->set($this->tempStoreId, $display->getConfiguration());
  }

  /**
   * @return \Drupal\Core\Database\Connection
   */
  private function database() {
    return \Drupal::database();
  }

  /**
   * @return \Drupal\panels\Storage\PanelsStorageManagerInterface
   */
  private function panelsStorage() {
    return \Drupal::service('panels.storage_manager');
  }

  /**
   * @return \Drupal\user\SharedTempStore
   */
  private function tempStore() {
    return \Drupal::service('user.shared_tempstore')->get('panels_ipe');
  }

}
