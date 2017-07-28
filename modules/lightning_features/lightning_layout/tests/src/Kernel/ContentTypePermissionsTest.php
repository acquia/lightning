<?php

namespace Drupal\Tests\lightning_layout\Kernel;

use Drupal\lightning_core\ConfigHelper as Config;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\token\Kernel\KernelTestBase;
use Drupal\user\Entity\Role;

/**
 * @group lightning
 * @group lightning_layout
 */
class ContentTypePermissionsTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'lightning_layout',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('user');

    /** @var \Drupal\user\RoleInterface $role */
    $role = Config::forModule('lightning_layout')
      ->optional()
      ->getEntity('user_role', 'layout_manager');

    $role->unsetThirdPartySetting('lightning', 'bundled')->save();
  }

  public function test() {
    $node_type = NodeType::create([
      'type' => $this->randomMachineName(),
    ]);
    $node_type->save();

    $role_id = 'layout_manager';
    $permission = 'administer panelizer node ' . $node_type->id() . ' defaults';

    $this->assertContains($permission, Role::load($role_id)->getPermissions());

    $node_type->delete();
    $this->assertNotContains($permission, Role::load($role_id)->getPermissions());
  }

}
