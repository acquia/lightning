<?php

namespace Drupal\Tests\lightning_roles\Kernel;

use Drupal\node\Entity\NodeType;
use Drupal\Tests\token\Kernel\KernelTestBase;
use Drupal\user\Entity\Role;

/**
 * @group lightning
 * @group lightning_roles
 */
class ContentRoleTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'lightning_roles',
    'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig('lightning_roles');
    $this->installEntitySchema('user');
  }

  public function test() {
    $node_type = NodeType::create([
      'type' => $this->randomMachineName(),
    ]);
    $node_type->save();

    $roles = [
      $node_type->id() . '_creator',
      $node_type->id() . '_reviewer',
    ];
    $this->assertCount(2, Role::loadMultiple($roles));

    $node_type->delete();
    $this->assertEmpty(Role::loadMultiple($roles));
  }

}
