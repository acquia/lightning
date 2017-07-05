<?php

namespace Drupal\lightning_inline_block;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * An interface for content entities that can be stored in a Panels display.
 */
interface InlineEntityInterface extends ContentEntityInterface {

  /**
   * @return \Drupal\lightning_inline_block\StorageContext
   */
  public function getStorageContext();

  public function setStorageContext(StorageContext $context);

}
