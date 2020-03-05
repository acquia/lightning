<?php

namespace Drupal\Tests\lightning\ExistingSite;

use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\user\Entity\Role;
use Drupal\user\UserInterface;
use Drupal\workflows\Entity\Workflow;
use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Ensures the integrity and correctness of Lightning's bundled config.
 *
 * @group lightning
 */
class ConfigIntegrityTest extends ExistingSiteBase {

  /**
   * {@inheritdoc}
   */
  protected static $configSchemaCheckerExclusions = [
    // @todo Remove this when depending on slick_entityreference 1.2 or later.
    'core.entity_view_display.block_content.media_slideshow.default',
  ];

  /**
   * Tests config set during install.
   */
  public function testConfig() {
    $assert_session = $this->assertSession();

    // Assert that all install tasks have done what they should do.
    // @see lightning_install_tasks()
    $account = \Drupal::entityTypeManager()
      ->getStorage('user')
      ->load(1);
    $this->assertInstanceOf(UserInterface::class, $account);
    /** @var \Drupal\user\UserInterface $account */
    $this->assertTrue($account->hasRole('administrator'));

    $this->assertSame('/node', $this->config('system.site')->get('page.front'));
    $this->assertSame(UserInterface::REGISTER_ADMINISTRATORS_ONLY, $this->config('user.settings')->get('register'));
    $this->assertTrue(Role::load(Role::AUTHENTICATED_ID)->hasPermission('access shortcuts'));
    $theme_config = $this->config('system.theme');
    $this->assertSame('bartik', $theme_config->get('default'));
    $this->assertSame('claro', $theme_config->get('admin'));
    $this->assertTrue($this->config('node.settings')->get('use_admin_theme'));
    $theme_global = $this->config('system.theme.global');
    $this->assertContains('/lightning/lightning.png', $theme_global->get('logo.path'));
    $this->assertContains('/lightning/favicon.ico', $theme_global->get('favicon.path'));
    /* @todo: Assert changes to the frontpage view were made. */

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
    // Media creators can use bulk upload.
    $this->assertPermissions('media_creator', 'dropzone upload files');

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
    $this->assertEntityExists('crop_type', 'freeform');
    $this->assertEntityExists('image_style', 'crop_freeform');

    // Assert that the editorial workflow exists and has the review state and
    // transition.
    $workflow = Workflow::load('editorial');
    $this->assertInstanceOf(Workflow::class, $workflow);
    /** @var \Drupal\workflows\WorkflowTypeInterface $type_plugin */
    $type_plugin = $workflow->getTypePlugin();
    // getState() throws an exception if the state does not exist.
    $type_plugin->getState('review');
    // getTransition() throws an exception if the transition does not exist.
    /** @var \Drupal\workflows\TransitionInterface $transition */
    $transition = $type_plugin->getTransition('review');
    $this->assertEquals('review', $transition->to()->id());
    $from = array_keys($transition->from());
    $this->assertContainsAll(['draft', 'review'], $from);
    $this->assertNotContains('published', $from);

    $creator_permissions = [
      'use text format rich_text',
      'access media_browser entity browser pages',
      'access image_browser entity browser pages',
    ];
    $this->assertPermissions('page_creator', $creator_permissions);
    $this->assertPermissions('landing_page_creator', $creator_permissions);
    $this->assertPermissions('layout_manager', [
      'administer node display',
      'configure any layout',
    ]);

    $node_types = \Drupal::entityQuery('node_type')->execute();

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

    // Assert that bundled content types have meta tags enabled.
    $this->assertMetatag(['page', 'landing_page']);

    // Assert that Lightning configuration pages are accessible to users who
    // have an administrative role.
    $this->assertForbidden('/admin/config/system/lightning');
    $this->assertForbidden('/admin/config/system/lightning/api');
    $this->assertForbidden('/admin/config/system/lightning/layout');
    $this->assertForbidden('/admin/config/system/lightning/media');

    $account = $this->createUser([], NULL, TRUE);
    $this->drupalLogin($account);

    $this->assertAllowed('/admin/config/system/lightning');
    $assert_session->linkByHrefExists('/admin/config/system/lightning/api');
    $assert_session->linkByHrefExists('/admin/config/system/lightning/layout');
    $assert_session->linkByHrefExists('/admin/config/system/lightning/media');
    $this->assertAllowed('/admin/config/system/lightning/api');
    $this->assertAllowed('/admin/config/system/lightning/api/keys');
    $this->assertAllowed('/admin/config/system/lightning/layout');
    $this->assertAllowed('/admin/config/system/lightning/media');
  }

  /**
   * Asserts that meta tags are enabled for specific content types.
   *
   * @param string[] $node_types
   *   The node type IDs to check.
   */
  private function assertMetatag(array $node_types) {
    $assert = $this->assertSession();

    $permissions = array_map(
      function ($node_type) {
        return "create $node_type content";
      },
      $node_types
    );
    $account = $this->createUser($permissions);
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
  private function assertEntityExists($entity_type, $id) {
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
  private function assertPermissions($role, $permissions) {
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
  private function assertContainsAll(array $needles, array $haystack) {
    $diff = array_diff($needles, $haystack);
    $this->assertSame([], $diff);
  }

  /**
   * Asserts that the current user can access a Drupal route.
   *
   * @param string $path
   *   The route path to visit.
   */
  private function assertAllowed($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(200);
  }

  /**
   * Asserts that the current user cannot access a Drupal route.
   *
   * @param string $path
   *   The route path to visit.
   */
  private function assertForbidden($path) {
    $this->drupalGet($path);
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Returns a config object by its name.
   *
   * @param string $name
   *   The name of the config object to return.
   *
   * @return \Drupal\Core\Config\Config
   *   The config object.
   */
  private function config($name) {
    return $this->container->get('config.factory')->getEditable($name);
  }

}
