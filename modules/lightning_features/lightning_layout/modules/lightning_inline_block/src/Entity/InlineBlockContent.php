<?php

namespace Drupal\lightning_inline_block\Entity;

use Drupal\block_content\Entity\BlockContent;
use Drupal\ctools_entity_mask\MaskEntityTrait;

/**
 * Defines the inline block entity class.
 *
 * @ContentEntityType(
 *   id = "inline_block_content",
 *   label = @Translation("Custom block"),
 *   bundle_label = @Translation("Custom block type"),
 *   handlers = {
 *     "storage" = "Drupal\lightning_inline_block\InlineBlockContentStorage",
 *     "access" = "Drupal\block_content\BlockContentAccessControlHandler",
 *     "view_builder" = "Drupal\block_content\BlockContentViewBuilder",
 *     "form" = {
 *       "panels_ipe" = "Drupal\lightning_inline_block\Form\InlineBlockContentForm"
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

  protected $storage = [];

  public function getStorage() {
    $this->storage['uuid'] = $this->uuid();
    return $this->storage;
  }

  public function setStorage(array $storage) {
    $this->storage = $storage;
    return $this;
  }

  public function getDisplay() {
    $storage = $this->getStorage();

    return \Drupal::service('panels.storage_manager')
      ->load($storage['storage_type'], $storage['storage_id']);
  }

  /**
   * The region of the Panels display in which this block is to be placed.
   *
   * @var string
   */
  public $region;

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    return array_diff(parent::__sleep(), ['storage']);
  }

}
