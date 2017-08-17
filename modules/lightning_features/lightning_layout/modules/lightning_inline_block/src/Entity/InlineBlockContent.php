<?php

namespace Drupal\lightning_inline_block\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\ctools_entity_mask\MaskEntityTrait;

/**
 * Defines the inline block entity class.
 *
 * @ContentEntityType(
 *   id = "inline_block_content",
 *   label = @Translation("Custom block"),
 *   bundle_label = @Translation("Custom block type"),
 *   handlers = {
 *     "storage" = "Drupal\lightning_inline_block\InlineEntityStorage",
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
 *     "view_builder" = "Drupal\lightning_inline_block\InlineEntityViewBuilder",
 *     "form" = {
 *       "edit" = "\Drupal\Core\Entity\ContentEntityForm",
 *     },
 *   },
 *   admin_permission = "administer blocks",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "info",
 *     "uuid" = "uuid"
 *   },
 *   bundle_entity_type = "block_content_type",
 *   render_cache = FALSE,
 *   mask = "block_content",
 * )
 */
class InlineBlockContent extends ContentEntityBase {

  use MaskEntityTrait;

  /**
   * {@inheritdoc}
   */
  public function access($operation, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $this->getHostEntity()->access($operation, $account, $return_as_object);
  }

  /**
   * {@inheritdoc}
   */
  public function __wakeup() {
    parent::__wakeup();
    // $this->original must be present so ContentEntityStorageBase::doPreSave()
    // doesn't think that we're trying to change the ID (in which case it will
    // throw an exception).
    $this->original = $this;
  }

  /**
   * Returns the entity of which this inline block is an intrinsic part.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The host entity.
   */
  public function getHostEntity() {
    $record = $this->database()
      ->select('inline_entity', 'ie')
      ->fields('ie', [
        'entity_type',
        'entity_id',
      ])
      ->condition('uuid', $this->uuid())
      ->execute()
      ->fetch();

    return $this->entityTypeManager()
      ->getStorage($record->entity_type)
      ->load($record->entity_id);
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $base_fields = parent::baseFieldDefinitions($entity_type);

    $base_fields['info'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE);

    return $base_fields;
  }

  /**
   * Returns the database connection.
   *
   * @return \Drupal\Core\Database\Connection
   *   The database connection.
   */
  protected function database() {
    return \Drupal::database();
  }

  public function getTheme() {
    return '';
  }

}
