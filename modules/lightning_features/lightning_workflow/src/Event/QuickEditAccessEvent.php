<?php

namespace Drupal\lightning_workflow\Event;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\EventDispatcher\Event;

class QuickEditAccessEvent extends Event {

  protected $entity;

  protected $access;

  public function __construct(EntityInterface $entity) {
    $this->entity = $entity;
    $this->access = AccessResult::neutral();
  }

  public function getEntity() {
    return $this->entity;
  }

  public function getAccess() {
    return $this->access;
  }

  public function setAccess(AccessResult $access) {
    $this->access = $access;
  }

}
