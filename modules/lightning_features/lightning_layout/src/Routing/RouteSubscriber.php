<?php

namespace Drupal\lightning_layout\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\lightning_layout\Controller\PanelsIpeController;
use Symfony\Component\Routing\RouteCollection;

/**
 * Performs routing alterations.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $collection
      ->get('panels_ipe.block_plugins')
      ->setDefault('_controller', PanelsIpeController::class . '::getBlockPlugins');
  }

}
