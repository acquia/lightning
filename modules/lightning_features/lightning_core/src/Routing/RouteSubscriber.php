<?php

namespace Drupal\lightning_core\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\lightning_core\Controller\FieldUiTitleController;
use Symfony\Component\Routing\RouteCollection;

/**
 * Dynamically alters various routes.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The affected entity type IDs.
   *
   * @var string[]
   */
  protected $entityTypes;

  /**
   * RouteSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $definitions = $entity_type_manager->getDefinitions();

    $filter = function (EntityTypeInterface $entity_type) {
      return (bool) $entity_type->get('field_ui_base_route');
    };
    $definitions = array_filter($definitions, $filter);

    $this->entityTypes = array_keys($definitions);
  }

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    foreach ($this->entityTypes as $entity_type) {
      // The 'Manage fields' page.
      $this->setBundleAsTitle('entity.' . $entity_type . '.field_ui_fields', $collection);

      // The default view display under 'Manage display'.
      $this->setBundleAsTitle('entity.entity_view_display.' . $entity_type . '.default', $collection);

      // A customized view display under 'Manage display'.
      $this->setBundleAsTitle('entity.entity_view_display.' . $entity_type . '.view_mode', $collection);

      // The default form display under 'Manage display'.
      $this->setBundleAsTitle('entity.entity_form_display.' . $entity_type . '.default', $collection);

      // A customized form display under 'Manage display'.
      $this->setBundleAsTitle('entity.entity_form_display.' . $entity_type . '.form_mode', $collection);
    }
  }

  /**
   * Sets FieldUiTitleController::bundle() as the title callback for a route.
   *
   * @param string $route_name
   *   The route name.
   * @param \Symfony\Component\Routing\RouteCollection $collection
   *   The complete route collection containing the route to alter.
   */
  protected function setBundleAsTitle($route_name, RouteCollection $collection) {
    $route = $collection->get($route_name);
    if ($route) {
      $route->setDefault('_title_callback', FieldUiTitleController::class . '::bundle');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();

    // We need to run after Field UI.
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -110];

    return $events;
  }

}
