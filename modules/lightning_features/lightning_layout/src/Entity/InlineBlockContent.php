<?php

namespace Drupal\lightning_layout\Entity;

use Drupal\block_content\Entity\BlockContent;
use Drupal\ctools_entity_mask\MaskEntityTrait;

/**
 * Defines the inline block entity class.
 *
 * @ContentEntityType(
 *   id = "inline_block_content",
 *   label = @Translation("Inline custom block"),
 *   bundle_label = @Translation("Custom block type"),
 *   handlers = {
 *     "storage" = "Drupal\Core\Entity\Sql\SqlContentEntityStorage",
 *     "access" = "Drupal\block_content\BlockContentAccessControlHandler",
 *     "view_builder" = "Drupal\block_content\BlockContentViewBuilder",
 *     "form" = {
 *       "panels_ipe" = "Drupal\lightning_layout\Form\InlineBlockContentForm"
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

}
