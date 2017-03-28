<?php

namespace Drupal\Tests\lightning_core\Kernel\Access;

use Drupal\Core\Routing\RouteMatch;
use Drupal\KernelTests\KernelTestBase;
use Drupal\lightning_core\Access\AdministrativeRoleCheck;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Symfony\Component\Routing\Route;

/**
 * @coversDefaultClass \Drupal\lightning_core\Access\AdministrativeRoleCheck
 *
 * @group lightning
 * @group lightning_core
 */
class AdministrativeRoleCheckTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['system', 'user'];

  /**
   * @covers ::access
   */
  public function testAccess() {
    $admin_role = $this->randomMachineName();

    Role::create([
      'id' => $admin_role,
      'label' => $admin_role,
      'is_admin' => TRUE,
    ])->save();

    $route = new Route('/foo');
    $route_match = new RouteMatch('foo', $route);

    $account = User::create();
    $account->addRole($admin_role);

    $access_check = new AdministrativeRoleCheck(
      $this->container->get('entity_type.manager')
    );

    $this->assertTrue($access_check->access($route, $route_match, $account)->isAllowed());

    $account->removeRole($admin_role);
    $this->assertTrue($access_check->access($route, $route_match, $account)->isForbidden());
  }

}
