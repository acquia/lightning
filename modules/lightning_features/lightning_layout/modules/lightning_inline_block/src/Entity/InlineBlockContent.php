<?php

namespace Drupal\lightning_inline_block\Entity;

use Drupal\block_content\Entity\BlockContent;
use Drupal\ctools_entity_mask\MaskEntityTrait;
use Drupal\lightning_inline_block\InlineEntityInterface;
use Drupal\lightning_inline_block\InlineEntityTrait;

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
 *     "view_builder" = "Drupal\block_content\BlockContentViewBuilder",
 *     "form" = {
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
  use InlineEntityTrait;

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    return array_diff(
      parent::__sleep(),
      ['display', 'tempStore', 'tempStoreId', 'configuration']
    );
  }

}
