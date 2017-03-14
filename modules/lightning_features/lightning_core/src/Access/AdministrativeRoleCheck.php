<?php

namespace Drupal\lightning_core\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;

class AdministrativeRoleCheck implements AccessInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * AdministratorRoleAccessCheck constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Checks if the user has at least one administrative role.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route being checked.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route matcher.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account to check.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function access(Route $route, RouteMatchInterface $route_match, AccountInterface $account) {
    /** @var \Drupal\user\RoleInterface[] $roles */
    $roles = $this->entityTypeManager
      ->getStorage('user_role')
      ->loadMultiple(
        $account->getRoles(TRUE)
      );

    foreach ($roles as $role) {
      if ($role->isAdmin()) {
        return AccessResult::allowed();
      }
    }
    return AccessResult::forbidden('The user must have at least one administrative role.');
  }

}
