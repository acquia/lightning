<?php

namespace Drupal\lightning_inline_block;

use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;

trait InlineEntityTrait {

  protected $panelsStorage;

  protected $tempStore;

  protected $display;

  protected $tempStoreId;

  protected $configuration = [];

  public function getDisplay() {
    return $this->display;
  }

  public function setDisplay(PanelsDisplayVariant $display, $temp_store_id = NULL) {
    $this->display = $display;
    $this->tempStoreId = $temp_store_id ?: $display->getTempStoreId();

    $configuration = $this->tempStore()->get($temp_store_id);
    if ($configuration) {
      $display->setConfiguration($configuration);
    }

    return $this;
  }

  public function setStorage($storage_type, $storage_id, $temp_store_id = NULL) {
    $display = $this->panelsStorage()->load($storage_type, $storage_id);
    return $this->setDisplay($display, $temp_store_id);
  }

  public function getConfiguration() {
    return $this->configuration;
  }

  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
    return $this;
  }

  public function getTempStoreId() {
    return $this->tempStoreId;
  }

  private function panelsStorage() {
    return $this->panelsStorage ?: \Drupal::service('panels.storage_manager');
  }

  private function tempStore() {
    return $this->tempStore ?: \Drupal::service('user.shared_tempstore')->get('panels_ipe');
  }

}
