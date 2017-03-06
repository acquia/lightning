<?php

namespace Drupal\lightning_workflow\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\lightning_workflow\Controller\PanelizerIPEController;
use Drupal\lightning_workflow\Form\NodeRevisionRevertForm;
use Symfony\Component\Routing\RouteCollection;

/**
 * Reacts to routing events.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $route = $collection->get('panelizer.panels_ipe.revert_to_default');
    if ($route) {
      $route->setDefault('_controller', PanelizerIPEController::class . '::revertToDefault');
    }

    $route = $collection->get('node.revision_revert_confirm');
    if ($route) {
      $route->setDefault('_form', NodeRevisionRevertForm::class);
    }
  }

}
