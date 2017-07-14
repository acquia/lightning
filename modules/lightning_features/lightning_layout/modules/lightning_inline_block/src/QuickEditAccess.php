<?php

namespace Drupal\lightning_inline_block;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\lightning_inline_block\Entity\InlineBlockContent;
use Drupal\lightning_workflow\Event\QuickEditAccessEvent;
use Drupal\lightning_workflow\QuickEditAccess as BaseQuickEditAccess;

class QuickEditAccess extends BaseQuickEditAccess {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * QuickEditAccess constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher service.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct($route_match, $event_dispatcher, Connection $database, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($route_match, $event_dispatcher);
    $this->database = $database;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function onQuickEditAccess(QuickEditAccessEvent $event) {
    $entity = $event->getEntity();

    // If the entity in question is an inline block, invoke the parent method
    // on the host entity.
    if ($entity instanceof InlineBlockContent) {
      /** @var \Drupal\lightning_workflow\Event\QuickEditAccessEvent $new_event */
      $delegate = new QuickEditAccessEvent($entity->getHostEntity());
      parent::onQuickEditAccess($delegate);
      $event->setAccess($delegate->getAccess());
    }
    else {
      parent::onQuickEditAccess($event);
    }
  }

}
