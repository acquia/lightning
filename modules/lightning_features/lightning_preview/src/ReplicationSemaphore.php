<?php

namespace Drupal\lightning_preview;

use Drupal\replication\Event\ReplicationEvent;
use Drupal\replication\Event\ReplicationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sets a semaphore during replication.
 */
class ReplicationSemaphore implements EventSubscriberInterface {

  /**
   * The replication event.
   *
   * @var ReplicationEvent
   */
  protected $event;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      ReplicationEvents::PRE_REPLICATION => [
        'onReplicationBegin',
      ],
      ReplicationEvents::POST_REPLICATION => [
        'onReplicationEnd',
      ],
    ];
  }

  /**
   * Returns the replication event.
   *
   * @return ReplicationEvent|null
   *   The replication event.
   */
  public function getEvent() {
    return $this->event;
  }

  /**
   * Reacts when a replication begins.
   *
   * @param ReplicationEvent $event
   *   The event object.
   */
  public function onReplicationBegin(ReplicationEvent $event) {
    $this->event = $event;
  }

  /**
   * Reacts when a replication ends.
   */
  public function onReplicationEnd() {
    $this->event = NULL;
  }

}
