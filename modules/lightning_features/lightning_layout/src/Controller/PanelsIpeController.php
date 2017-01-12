<?php

namespace Drupal\lightning_layout\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\panels_ipe\Controller\PanelsIPEPageController;

/**
 * Custom page controller for Panels IPE.
 */
class PanelsIpeController extends PanelsIPEPageController {

  /**
   * {@inheritdoc}
   */
  public function getBlockPlugins($panels_storage_type, $panels_storage_id) {
    $categories = $this->config('lightning_layout.settings')
      ->get('ipe.categories');

    $map = [];
    foreach ($categories as $category) {
      foreach ($category['blocks'] as $plugin_id) {
        $map[$plugin_id] = $category['label'];
      }
    }

    $response = parent::getBlockPlugins($panels_storage_type, $panels_storage_id);
    $data = Json::decode($response->getContent());

    foreach ($data as &$plugin_definition) {
      $id = $plugin_definition['plugin_id'];

      if (isset($map[$id])) {
        $plugin_definition['category'] = $map[$id];
      }
    }

    return $response->setData($data);
  }

}
