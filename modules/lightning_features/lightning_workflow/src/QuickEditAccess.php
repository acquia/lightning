<?php

namespace Drupal\lightning_workflow;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\lightning_workflow\Event\QuickEditAccessEvent;
use Drupal\lightning_workflow\Event\QuickEditEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class QuickEditAccess implements EventSubscriberInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * QuickEditAccess constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   */
  public function __construct(RouteMatchInterface $route_match, EventDispatcherInterface $event_dispatcher) {
    $this->routeMatch = $route_match;
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      QuickEditEvents::ACCESS => 'onQuickEditAccess',
    ];
  }

  /**
   * Determines if Quick Edit can be accessed for an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function getAccess(EntityInterface $entity) {
    $event = new QuickEditAccessEvent($entity);

    // An event subscriber that dispatches events! Ain't mad science fun?
    $this->eventDispatcher->dispatch(QuickEditEvents::ACCESS, $event);

    return $event->getAccess();
  }

  /**
   * Reacts to the QuickEditEvents::ACCESS event.
   *
   * @param \Drupal\lightning_workflow\Event\QuickEditAccessEvent $event
   *   The event object.
   */
  public function onQuickEditAccess(QuickEditAccessEvent $event) {
    $entity = $event->getEntity();

    $event->setAccess(
      AccessResult::forbiddenIf(
       $entity instanceof EntityPublishedInterface &&
       $entity->isPublished() &&
       $this->atCanonical($entity)
      )
    );
  }

  /**
   * Checks if the current route is the canonical route for an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return bool
   *   TRUE if the current route is the canonical entity route, FALSE otherwise.
   */
  protected function atCanonical(EntityInterface $entity) {
    $entity_type = $entity->getEntityTypeId();

    return
      $this->routeMatch->getRouteName() == "entity.$entity_type.canonical" &&
      $this->routeMatch->getParameter($entity_type)->id() === $entity->id();
  }

}
