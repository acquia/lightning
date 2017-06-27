<?php

namespace Drupal\lightning_media_image\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\lightning_media_image\Form\EntityEmbedDialog;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $route = $collection->get('entity_embed.dialog');
    if ($route) {
      $route->setDefault('_form', EntityEmbedDialog::class);
    }
  }

}
