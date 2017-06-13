<?php

namespace Drupal\lightning\Tests\Functional;

use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Tests\BrowserTestBase;
use Drupal\user\Entity\Role;

/**
 * Ensures the integrity and correctness of Lightning's bundled config.
 *
 * @group lightning
 */
class ConfigIntegrityTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'lightning';

  public function testConfig() {
    // lightning_core_update_8002() marks a couple of core view modes as
    // internal.
    $view_modes = EntityViewMode::loadMultiple(['node.rss', 'node.search_index']);
    /** @var \Drupal\Core\Entity\EntityViewModeInterface $view_mode */
    foreach ($view_modes as $view_mode) {
      $this->assertTrue($view_mode->getThirdPartySetting('lightning_core', 'internal'));
    }

    // lightning_layout_update_8003() grants layout_manager role Panelizer
    // permissions for every node type.
    $permissions = Role::load('layout_manager')->getPermissions();
    foreach (\Drupal::entityQuery('node_type')->execute() as $node_type) {
      $this->assertContains('administer panelizer node ' . $node_type . ' defaults', $permissions);
    }

    // All users should be able to view media items.
    $this->assertContains('view media', Role::load('anonymous')->getPermissions());
    $this->assertContains('view media', Role::load('authenticated')->getPermissions());
  }

}
