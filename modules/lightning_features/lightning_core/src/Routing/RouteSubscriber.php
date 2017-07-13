<?php

namespace Drupal\lightning_core\Routing;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Drupal\lightning_core\Controller\FieldUiTitleController;
use Symfony\Component\Routing\RouteCollection;

/**
 * Dynamically alters various routes.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * RouteSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    foreach ($this->entityTypeManager->getDefinitions() as $id => $entity_type) {
      if ($entity_type->get('field_ui_base_route') == NULL) {
        continue;
      }

      // The 'Manage fields' page.
      $this->setBundleAsTitle("entity.$id.field_ui_fields", $collection);

      // The default view display under 'Manage display'.
      $this->setBundleAsTitle("entity.entity_view_display.$id.default", $collection);

      // A customized view display under 'Manage display'.
      $this->setBundleAsTitle("entity.entity_view_display.$id.view_mode", $collection);

      // The default form display under 'Manage display'.
      $this->setBundleAsTitle("entity.entity_form_display.$id.default", $collection);

      // A customized form display under 'Manage display'.
      $this->setBundleAsTitle("entity.entity_form_display.$id.form_mode", $collection);
    }
  }

  /**
   * Checks if we are currently viewing an entity at its canonical route.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\Core\Routing\RouteMatchInterface|NULL $route_match
   *   (optional) The current route match.
   *
   * @return bool
   *   TRUE if we are at the entity's canonical route, FALSE otherwise.
   */
  public static function isViewing(EntityInterface $entity, RouteMatchInterface $route_match = NULL) {
    $route_match = $route_match ?: \Drupal::routeMatch();

    $entity_type = $entity->getEntityTypeId();

    return
      $route_match->getRouteName() == "entity.$entity_type.canonical" &&
      $route_match->getRawParameter($entity_type) == $entity->id();
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
