<?php

namespace Drupal\lightning_inline_block;

use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

/**
 * Provides a base implementation of InlineEntityInterface.
 */
trait InlineEntityTrait {

  /**
   * The Panels storage manager.
   *
   * @var \Drupal\panels\Storage\PanelsStorageManagerInterface
   */
  protected $panelsStorage;

  /**
   * The Panels IPE temp store.
   *
   * @var \Drupal\user\SharedTempStore
   */
  protected $tempStore;

  /**
   * The Panels display.
   *
   * @var PanelsDisplayVariant
   */
  protected $display;

  /**
   * The temp store ID of the Panels display.
   *
   * @var string
   */
  protected $tempStoreId;

  /**
   * The inline_entity block configuration.
   *
   * @var array
   */
  protected $configuration = [];

  /**
   * Implements InlineEntityInterface::getDisplay().
   */
  public function getDisplay() {
    return $this->display;
  }

  /**
   * Implements InlineEntityInterface::setDisplay().
   */
  public function setDisplay(PanelsDisplayVariant $display, $temp_store_id = NULL) {
    $this->display = $display;
    $this->tempStoreId = $temp_store_id ?: $display->getTempStoreId();

    $configuration = $this->tempStore()->get($temp_store_id);
    if ($configuration) {
      $display->setConfiguration($configuration);
    }

    return $this;
  }

  /**
   * Implements InlineEntityInterface::setStorage().
   */
  public function setStorage($storage_type, $storage_id, $temp_store_id = NULL) {
    $display = $this->panelsStorage()->load($storage_type, $storage_id);
    return $this->setDisplay($display, $temp_store_id);
  }

  /**
   * Implements InlineEntityInterface::getConfiguration().
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * Implements InlineEntityInterface::setConfiguration().
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
    return $this;
  }

  /**
   * Implements InlineEntityInterface::getTempStoreId().
   */
  public function getTempStoreId() {
    return $this->tempStoreId;
  }

  /**
   * Returns the Panels storage manager.
   *
   * @return \Drupal\panels\Storage\PanelsStorageManagerInterface
   *   The Panels storage manager.
   */
  private function panelsStorage() {
    return $this->panelsStorage ?: \Drupal::service('panels.storage_manager');
  }

  /**
   * Returns the Panels IPE temp store.
   *
   * @return \Drupal\user\SharedTempStore
   *   The Panels IPE temp store.
   */
  private function tempStore() {
    return $this->tempStore ?: \Drupal::service('user.shared_tempstore')->get('panels_ipe');
  }

}
