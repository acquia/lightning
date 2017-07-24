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
    $assert = $this->assertSession();

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

    foreach (\Drupal::entityQuery('node_type')->execute() as $node_type) {
      $needles = [
        "create $node_type content",
        "edit own $node_type content",
        "view $node_type revisions",
        'view own unpublished content',
        'create url aliases',
        'access in-place editing',
        'access contextual links',
        'access toolbar',
      ];
      $haystack = Role::load("{$node_type}_creator")->getPermissions();
      $this->assertContainsAll($needles, $haystack);

      $needles = [
        'access content overview',
        "edit any $node_type content",
        "delete any $node_type content",
      ];
      $haystack = Role::load("{$node_type}_reviewer")->getPermissions();
      $this->assertContainsAll($needles, $haystack);
    }

    $account = $this->drupalCreateUser();
    $this->drupalLogin($account);

    $this->assertForbidden('/admin/config/system/lightning');
    $this->assertForbidden('/admin/config/system/lightning/layout');
    $this->assertForbidden('/admin/config/system/lightning/media');

    $this->drupalLogout();
    $account = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($account);

    $this->assertAllowed('/admin/config/system/lightning');
    $assert->linkByHrefExists('/admin/config/system/lightning/layout');
    $assert->linkByHrefExists('/admin/config/system/lightning/media');
    $this->assertAllowed('/admin/config/system/lightning/layout');
    $this->assertAllowed('/admin/config/system/lightning/media');

    // Assert that public and private keys were generated for OAuth.
    $oauth = $this->config('simple_oauth.settings');

    $private_key = $oauth->get('private_key');
    $this->assertNotEmpty($private_key);
    $this->assertFileExists($private_key);

    $public_key = $oauth->get('public_key');
    $this->assertNotEmpty($public_key);
    $this->assertFileExists($public_key);
  }

  /**
   * Asserts that a haystack contains a set of needles.
   *
   * @param mixed[] $needles
   *   The needles expected to be in the haystack.
   * @param mixed[] $haystack
   *   The haystack.
   */
  protected function assertContainsAll(array $needles, array $haystack) {
    $diff = array_diff($needles, $haystack);
    $this->assertEmpty($diff);
  }

  /**
   * Asserts that the current user can access a Drupal route.
   *
   * @param string $path
   *   The route path to visit.
   */
  protected function assertAllowed($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Asserts that the current user cannot access a Drupal route.
   *
   * @param string $path
   *   The route path to visit.
   */
  protected function assertForbidden($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(403);
  }

}
