<?php

namespace Drupal\lightning_workflow;

/**
 * Provides data to Views (i.e., via hook_views_data()).
 */
class ViewsData {

  /**
   * Returns all relevant data for Views.
   *
   * @return array
   *   The data exposed to Views, in the format expected by hook_views_data().
   */
  public function getAll() {
    return [];
  }

}
