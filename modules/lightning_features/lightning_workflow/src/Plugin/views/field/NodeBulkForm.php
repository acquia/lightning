<?php

namespace Drupal\lightning_workflow\Plugin\views\field;

use Drupal\node\Plugin\views\field\NodeBulkForm as BaseNodeBulkForm;

/**
 * Extends the node_bulk_form field plugin to disallow certain options.
 */
class NodeBulkForm extends BaseNodeBulkForm {

  /**
   * {@inheritdoc}
   */
  protected function getBulkOptions($filtered = TRUE) {
    $options = parent::getBulkOptions($filtered);
    unset($options['node_publish_action'], $options['node_unpublish_action']);

    return $options;
  }

}
