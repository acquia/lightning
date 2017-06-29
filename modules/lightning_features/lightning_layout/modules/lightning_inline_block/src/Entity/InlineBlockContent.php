<?php

namespace Drupal\lightning_inline_block\Entity;

use Drupal\block_content\Entity\BlockContent;
use Drupal\ctools_entity_mask\MaskEntityTrait;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

/**
 * Defines the inline block entity class.
 *
 * @ContentEntityType(
 *   id = "inline_block_content",
 *   label = @Translation("Inline custom block"),
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

  /**
   * The Panels display of which this block is an intrinsic part.
   *
   * Inline blocks do not have any existence outside of a Panels display, so
   * this is always required in order to save or load an inline block.
   *
   * @var \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant
   */
  protected $display;

  /**
   * The UUID of the block in the Panels display.
   *
   * If this is not set, it can be assumed that the block has not yet been added
   * to the Panels display.
   *
   * @var string
   */
  protected $blockId;

  /**
   * The temp store ID of the Panels display.
   *
   * A given display's temp store ID can vary depending on which automatic
   * contexts are available and what their values are. Generally an inline block
   * will be associated with a specific temp store ID, so although we can ask
   * the Panels display for its temp store ID, we cannot be certain that it will
   * be the temp store ID which is associated with this block. Therefore this
   * may need to be explicitly set.
   *
   * @var string
   */
  protected $tempStoreKey;

  public function getStorageContext() {
    return [
      $this->display,
      $this->blockId,
      $this->tempStoreKey ?: $this->display->getTempStoreId(),
    ];
  }

  public function setStorageContext(PanelsDisplayVariant $display, $block_id = NULL, $temp_store_key = NULL) {
    $this->display = $display;
    $this->blockId = $block_id;
    $this->tempStoreKey = $temp_store_key;

    return $this;
  }

}
