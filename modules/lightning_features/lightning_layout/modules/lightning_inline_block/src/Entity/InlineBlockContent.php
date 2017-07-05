<?php

namespace Drupal\lightning_inline_block\Entity;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\ctools_entity_mask\MaskEntityTrait;
use Drupal\lightning_inline_block\InlineEntityInterface;
use Drupal\lightning_inline_block\StorageContext;

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
class InlineBlockContent extends BlockContent implements InlineEntityInterface {

  use MaskEntityTrait;

  protected $storageContext;

  /**
   * {@inheritdoc}
   */
  public function getStorageContext() {
    return $this->storageContext;
  }

  /**
   * {@inheritdoc}
   */
  public function setStorageContext(StorageContext $context) {
    $this->storageContext = $context;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    $this->tempStore()->set($this->uuid(), $this->getStorageContext());

    return array_diff(parent::__sleep(), ['original', 'storageContext']);
  }

  /**
   * {@inheritdoc}
   */
  public function __wakeup() {
    parent::__wakeup();

    $this->original = $this;

    $temp_store = $this->tempStore();
    $key = $this->uuid();

    $context = $temp_store->get($key);
    if ($context) {
      $this->setStorageContext($context);
    }
  }

  /**
   * @return \Drupal\user\SharedTempStore
   */
  private function tempStore() {
    return \Drupal::service('user.shared_tempstore')->get('inline_entity');
  }

}
