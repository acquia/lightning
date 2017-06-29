<?php

namespace Drupal\lightning_inline_block\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\lightning_inline_block\Controller\PanelsIPEController;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $collection
      ->get('panels_ipe.block_content.form')
      ->setDefault('_controller', PanelsIPEController::class . '::getBlockContentForm');
  }

}
