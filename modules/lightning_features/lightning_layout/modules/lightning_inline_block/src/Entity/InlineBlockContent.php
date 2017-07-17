<?php

namespace Drupal\lightning_inline_block\Entity;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Entity\EntityTypeInterface;
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
 *     "access" = "Drupal\block_content\BlockContentAccessControlHandler",
 *     "view_builder" = "Drupal\lightning_inline_block\InlineBlockContentViewBuilder",
 *     "form" = {
 *       "edit" = "\Drupal\block_content\BlockContentForm",
 *       "panels_ipe" = "Drupal\lightning_inline_block\Form\InlineContentForm"
 *     },
 *     "translation" = "Drupal\block_content\BlockContentTranslationHandler"
 *   },
 *   admin_permission = "administer blocks",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "revision_id",
 *     "bundle" = "type",
 *     "label" = "info",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid"
 *   },
 *   bundle_entity_type = "block_content_type",
 *   render_cache = FALSE,
 *   mask = "block_content",
 * )
 */
class InlineBlockContent extends BlockContent {

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
    $this->original = $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['info']->setRequired(FALSE);

    return $fields;
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
   * Returns the database connection.
   *
   * @return \Drupal\Core\Database\Connection
   *   The database connection.
   */
  protected function database() {
    return \Drupal::database();
  }

}
