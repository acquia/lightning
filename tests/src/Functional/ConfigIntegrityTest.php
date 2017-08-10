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

    // All users should be able to view media items.
    $this->assertPermissions('anonymous', 'view media');
    $this->assertPermissions('authenticated', 'view media');

    $this->assertEntityExists('node_type', [
      'page',
      'landing_page',
    ]);
    $this->assertEntityExists('user_role', [
      'landing_page_creator',
      'landing_page_reviewer',
      'layout_manager',
      'media_creator',
      'media_manager',
      'page_creator',
      'page_reviewer',
    ]);

    $permissions = [
      'use text format rich_text',
      'access media_browser entity browser pages',
      'access image_browser entity browser pages',
    ];
    $this->assertPermissions('page_creator', $permissions);
    $this->assertPermissions('landing_page_creator', $permissions);

    $node_types = \Drupal::entityQuery('node_type')->execute();

    $permissions = [
      'administer node display',
      'administer panelizer',
    ];
    // lightning_layout_update_8003() grants layout_manager role Panelizer
    // permissions for every node type.
    foreach ($node_types as $node_type) {
      $permissions[] = "administer panelizer node $node_type defaults";
    }
    $this->assertPermissions('layout_manager', $permissions);

    foreach ($node_types as $node_type) {
      $this->assertPermissions("{$node_type}_creator", [
        "create $node_type content",
        "edit own $node_type content",
        "view $node_type revisions",
        'view own unpublished content',
        'create url aliases',
        'access in-place editing',
        'access contextual links',
        'access toolbar',
      ]);
      $this->assertPermissions("{$node_type}_reviewer", [
        'access content overview',
        "edit any $node_type content",
        "delete any $node_type content",
      ]);
    }

    // Assert that the site-wide contact form has the expected fields.
    $this->assertAllowed('/contact');
    $assert->fieldExists('Your name');
    $assert->fieldExists('Your email address');
    $assert->fieldExists('Subject');
    $assert->fieldExists('Message');

    // The name and e-mail fields should not be present for authenticated users.
    $account = $this->drupalCreateUser();
    $this->drupalLogin($account);
    $this->assertAllowed('/contact');
    $assert->fieldNotExists('Your name');
    $assert->fieldNotExists('Your email address');
    $assert->fieldExists('Subject');
    $assert->fieldExists('Message');
    $this->drupalLogout();

    // Assert that bundled content types have meta tags enabled.
    $this->assertMetatag(['page', 'landing_page']);

    // Assert that basic blocks expose a Body field.
    $account = $this->drupalCreateUser(['administer blocks']);
    $this->drupalLogin($account);
    $this->assertAllowed('/block/add');
    $assert->fieldExists('Body');
    $this->drupalLogout();

    // Assert that reviewer roles can view moderation states.
    $permission = 'view moderation states';
    $this->assertPermissions('page_reviewer', $permission);
    $this->assertPermissions('landing_page_reviewer', $permission);

    // Assert that Lightning configuration pages are accessible to users who
    // have an administrative role.
    $this->assertForbidden('/admin/config/system/lightning');
    $this->assertForbidden('/admin/config/system/lightning/api');
    $this->assertForbidden('/admin/config/system/lightning/layout');
    $this->assertForbidden('/admin/config/system/lightning/media');

    $account = $this->drupalCreateUser([], NULL, TRUE);
    $this->drupalLogin($account);

    $this->assertAllowed('/admin/config/system/lightning');
    $assert->linkByHrefExists('/admin/config/system/lightning/api');
    $assert->linkByHrefExists('/admin/config/system/lightning/layout');
    $assert->linkByHrefExists('/admin/config/system/lightning/media');
    $this->assertAllowed('/admin/config/system/lightning/api');
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
   * Asserts that meta tags are enabled for specific content types.
   *
   * @param string[] $node_types
   *   The node type IDs to check.
   */
  protected function assertMetatag(array $node_types) {
    $assert = $this->assertSession();

    $permissions = array_map(
      function ($node_type) {
        return "create $node_type content";
      },
      $node_types
    );
    $account = $this->drupalCreateUser($permissions);
    $this->drupalLogin($account);

    foreach ($node_types as $node_type) {
      $this->assertAllowed("/node/add/$node_type");
      $assert->fieldExists('field_meta_tags[0][basic][title]');
      $assert->fieldExists('field_meta_tags[0][basic][description]');
    }
    $this->drupalLogout();
  }

  /**
   * Asserts the existence of an entity.
   *
   * @param string $entity_type
   *   The entity type ID.
   * @param mixed|mixed[] $id
   *   The entity ID, or a set of IDs.
   */
  protected function assertEntityExists($entity_type, $id) {
    $this->assertContainsAll(
      (array) $id,
      \Drupal::entityQuery($entity_type)->execute()
    );
  }

  /**
   * Asserts that a user role has a set of permissions.
   *
   * @param \Drupal\user\RoleInterface|string $role
   *   The user role, or its ID.
   * @param string|string[] $permissions
   *   The permission(s) to check.
   */
  protected function assertPermissions($role, $permissions) {
    if (is_string($role)) {
      $role = Role::load($role);
    }
    $this->assertContainsAll((array) $permissions, $role->getPermissions());
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
