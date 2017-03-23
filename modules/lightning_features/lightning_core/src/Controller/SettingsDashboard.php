<?php

namespace Drupal\lightning_core\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * The main controller for configuring Lightning's behavior.
 */
class SettingsDashboard extends ControllerBase {

  /**
   * Generates the settings dashboard.
   *
   * @return array
   *   The renderable settings dashboard.
   */
  public function dashboard() {
    return [
      '#markup' => $this->t('Click the tabs above to configure various aspects of Lightning\'s behavior.'),
    ];
  }

}
