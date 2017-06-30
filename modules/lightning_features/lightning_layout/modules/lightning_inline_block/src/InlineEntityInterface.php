<?php

namespace Drupal\lightning_inline_block;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

/**
 * An interface for content entities that can be stored in a Panels display.
 */
interface InlineEntityInterface extends ContentEntityInterface {

  /**
   * Returns the Panels display which contains the entity.
   *
   * @return \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant
   *   The Panels display which contains the entity.
   */
  public function getDisplay();

  public function setDisplay(PanelsDisplayVariant $display, $temp_store_id = NULL);

  /**
   * @param $storage_type
   * @param $storage_id
   * @param null $temp_store_id
   *
   * @return $this
   */
  public function setStorage($storage_type, $storage_id, $temp_store_id = NULL);

  public function getConfiguration();

  public function setConfiguration(array $configuration);

  public function getTempStoreId();

}
