<?php

namespace Drupal\lightning_media\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\lightning_media\Form\EntityEmbedDialog;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $collection
      ->get('entity_embed.dialog')
      ->setDefault('_form', EntityEmbedDialog::class);
  }

}
