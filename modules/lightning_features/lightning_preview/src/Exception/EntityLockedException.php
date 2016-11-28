<?php

namespace Drupal\lightning_preview\Exception;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Exception thrown on an illegal entity operation in a locked workspace.
 */
class EntityLockedException extends \LogicException {

  /**
   * The entity that caused the exception.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * WorkspaceLockException constructor.
   *
   * @param EntityInterface $entity
   *   The entity that caused the exception.
   * @param \Exception $previous
   *   (optional) The previous exception.
   */
  public function __construct(EntityInterface $entity, \Exception $previous = NULL) {
    if ($entity instanceof ConfigEntityInterface) {
      $message = 'Configuration can only be modified in the Live workspace';
    }
    else {
      $message = 'Content cannot be modified in a locked workspace';
    }
    parent::__construct($message, 0, $previous);

    $this->entity = $entity;
  }

  /**
   * Returns the entity that caused the exception.
   *
   * @return EntityInterface
   *   The entity that caused the exception.
   */
  public function getEntity() {
    return $this->entity;
  }

}
