<?php

namespace Drupal\lightning_workflow\Event;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\EventDispatcher\Event;

class QuickEditAccessEvent extends Event {

  /**
   * The entity for which Quick Edit access is being determined.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The access result.
   *
   * @var \Drupal\Core\Access\AccessResultNeutral
   */
  protected $access;

  /**
   * QuickEditAccessEvent constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which Quick Edit access is being determined.
   */
  public function __construct(EntityInterface $entity) {
    $this->entity = $entity;
    $this->access = AccessResult::neutral();
  }

  /**
   * Returns the entity for which Quick Edit is being determined.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Returns the access result.
   *
   * @return \Drupal\Core\Access\AccessResultNeutral
   *   The access result.
   */
  public function getAccess() {
    return $this->access;
  }

  /**
   * Sets the access result.
   *
   * @param \Drupal\Core\Access\AccessResult $access
   *   The access result.
   */
  public function setAccess(AccessResult $access) {
    $this->access = $access;
  }

}
