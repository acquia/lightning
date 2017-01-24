<?php

namespace Acquia\LightningExtension\Context;

use Drupal\block\Entity\Block;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * A context for working with blocks and the core block system.
 */
class BlockContext extends DrupalSubContextBase {

  /**
   * The Mink context.
   *
   * @var MinkContext
   */
  protected $minkContext;

  /**
   * Gathers required contexts.
   *
   * @BeforeScenario
   */
  public function gatherContexts() {
    $this->minkContext = $this->getContext(MinkContext::class);
  }

  /**
   * Instantiates a specific block plugin in a specific theme.
   *
   * @param string $plugin_id
   *   The block plugin ID.
   * @param string $theme
   *   The theme key.
   *
   * @When I instantiate the :plugin_id block in :theme
   */
  public function instantiateBlock($plugin_id, $theme) {
    $this->visitPath('/admin/structure/block/add/' . $plugin_id . '/' . $theme);

    $id = uniqid($theme . '_');
    $this->minkContext->fillField('id', $id);

    /** @var UndoContext $undo */
    $undo = $this->getContext(UndoContext::class);
    if ($undo) {
      $undo->push([$this, 'deleteBlock'], [$id]);
    }
  }

  /**
   * Deletes a block.
   *
   * @param string $id
   *   The block entity ID.
   */
  public function deleteBlock($id) {
    $block = Block::load($id);
    if ($block) {
      $block->delete();
    }
  }

}
