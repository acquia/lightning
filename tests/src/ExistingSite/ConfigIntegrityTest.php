<?php

namespace Drupal\Tests\lightning\ExistingSite;

use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\user\Entity\Role;
use Drupal\user\UserInterface;
use Drupal\views\Entity\View;
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
  protected function setUp() {
    parent::setUp();

    // If the samlauth module is installed, ensure that it is configured (in
    // this case, using its own test data, copied here so as to not depend on
    // another module's test fixtures) to avoid errors when creating user
    // accounts in this test.
    if ($this->container->get('module_handler')->moduleExists('samlauth')) {
      $this->container->get('config.factory')
        ->getEditable('samlauth.authentication')
        ->setData([
          'sp_entity_id' => 'samlauth',
          'idp_entity_id' => 'https://idp.testshib.org/idp/shibboleth',
          'idp_single_sign_on_service' => 'https://idp.testshib.org/idp/profile/SAML2/Redirect/SSO',
          'idp_single_log_out_service' => '',
          'idp_x509_certificate' => 'MIIEDjCCAvagAwIBAgIBADANBgkqhkiG9w0BAQUFADBnMQswCQYDVQQGEwJVUzEVMBMGA1UECBMMUGVubnN5bHZhbmlhMRMwEQYDVQQHEwpQaXR0c2J1cmdoMREwDwYDVQQKEwhUZXN0U2hpYjEZMBcGA1UEAxMQaWRwLnRlc3RzaGliLm9yZzAeFw0wNjA4MzAyMTEyMjVaFw0xNjA4MjcyMTEyMjVaMGcxCzAJBgNVBAYTAlVTMRUwEwYDVQQIEwxQZW5uc3lsdmFuaWExEzARBgNVBAcTClBpdHRzYnVyZ2gxETAPBgNVBAoTCFRlc3RTaGliMRkwFwYDVQQDExBpZHAudGVzdHNoaWIub3JnMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEArYkCGuTmJp9eAOSGHwRJo1SNatB5ZOKqDM9ysg7CyVTDClcpu93gSP10nH4gkCZOlnESNgttg0r+MqL8tfJC6ybddEFB3YBo8PZajKSe3OQ01Ow3yT4I+Wdg1tsTpSge9gEz7SrC07EkYmHuPtd71CHiUaCWDv+xVfUQX0aTNPFmDixzUjoYzbGDrtAyCqA8f9CN2txIfJnpHE6q6CmKcoLADS4UrNPlhHSzd614kR/JYiks0K4kbRqCQF0Dv0P5Di+rEfefC6glV8ysC8dB5/9nb0yh/ojRuJGmgMWHgWk6h0ihjihqiu4jACovUZ7vVOCgSE5Ipn7OIwqd93zp2wIDAQABo4HEMIHBMB0GA1UdDgQWBBSsBQ869nh83KqZr5jArr4/7b+QazCBkQYDVR0jBIGJMIGGgBSsBQ869nh83KqZr5jArr4/7b+Qa6FrpGkwZzELMAkGA1UEBhMCVVMxFTATBgNVBAgTDFBlbm5zeWx2YW5pYTETMBEGA1UEBxMKUGl0dHNidXJnaDERMA8GA1UEChMIVGVzdFNoaWIxGTAXBgNVBAMTEGlkcC50ZXN0c2hpYi5vcmeCAQAwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOCAQEAjR29PhrCbk8qLN5MFfSVk98t3CT9jHZoYxd8QMRLI4j7iYQxXiGJTT1FXs1nd4Rha9un+LqTfeMMYqISdDDI6tv8iNpkOAvZZUosVkUo93pv1T0RPz35hcHHYq2yee59HJOco2bFlcsH8JBXRSRrJ3Q7Eut+z9uo80JdGNJ4/SJy5UorZ8KazGj16lfJhOBXldgrhppQBb0Nq6HKHguqmwRfJ+WkxemZXzhediAjGeka8nz8JjwxpUjAiSWYKLtJhGEaTqCYxCCX2Dw+dOTqUzHOZ7WKv4JXPK5G/Uhr8K/qhmFT2nIQi538n6rVYLeWj8Bbnl+ev0peYzxFyF5sQA==',
          'unique_id_attribute' => 'urn:oid:1.3.6.1.4.1.5923.1.1.1.10',
          'create_users' => 0,
          'sync_name' => 0,
          'sync_mail' => 0,
          'user_name_attribute' => 'email',
          'user_mail_attribute' => 'email',
          'idp_change_password_service' => '',
          'sp_x509_certificate' => 'MIIDozCCAoqgAwIBAgIBADANBgkqhkiG9w0BAQ0FADBrMQswCQYDVQQGEwJ1czEOMAwGA1UECAwFSWRhaG8xFTATBgNVBAoMDGN3ZWFnYW5zLm5ldDEVMBMGA1UEAwwMY3dlYWdhbnMubmV0MR4wHAYJKoZIhvcNAQkBFg9tZUBjd2VhZ2Fucy5uZXQwHhcNMTUwNjIzMjAwMjMyWhcNMjUwNjIwMjAwMjMyWjBrMQswCQYDVQQGEwJ1czEOMAwGA1UECAwFSWRhaG8xFTATBgNVBAoMDGN3ZWFnYW5zLm5ldDEVMBMGA1UEAwwMY3dlYWdhbnMubmV0MR4wHAYJKoZIhvcNAQkBFg9tZUBjd2VhZ2Fucy5uZXQwggEjMA0GCSqGSIb3DQEBAQUAA4IBEAAwggELAoIBAgDDZOeQF9Cp5k0WzNBye9S/3FgKxTZjcAPBFLtMMAhcx9+kLYMwS5J5h1OUKQcaoxmz/MiVKnrnozStdOKYIeS0C+8DmjRPjKEva77RYEy/Zu4l2Y+Nijt9/OMrO2JwuchHI9Xx+rqifDCR9rJ4vwbu/6/NhTVggSgsDsxlgGtLWC1zoUmwtcBe30t63P1eDrNAEg5EkM3y6OCsx6HaK7nAJmGaF6of/60UmEXB6qBVgZlU/qUmrVX89EdGvPrKWvYJcX3xAcIQh/on/1e/XmGMRYnBB6E0qyx6sL0ZmHzwH5jIUR5S1xwqWhSAjlOUHLSg2tYfHx0dn3UV2koY9QsKEQIDAQABo1AwTjAdBgNVHQ4EFgQUTJG5GAzq0olNiSfg7c/zjaBHnwcwHwYDVR0jBBgwFoAUTJG5GAzq0olNiSfg7c/zjaBHnwcwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQ0FAAOCAQIAsaxgtTmqQLbNETa7pLD0q0qCU7awxUUfwM73CwW+uIoZXeTz4YP6qaQ9T8kbBjyQKK9jmvTCHHm7y7U7a6NTj3DHuGpjo6mOXNMeh259iCSOfxpm2hMnUsLuQk3dM1+POJWSRQ8LFUcx9WT7siqvKfKq8cTjul7DMRR3MWOAgcd6N+ru3UYsuU81M0Gar417va+GkdMhoRBGT/K6jz9dkvOdQWmIlitYGvkQ6o7VGM5wB9J9wZ/A5FDJ0/IxGXJDD8aFtst8RTBwf8Cgptbmmycu9jqrby4bDLQ5ygviBZ7ZvXt4c9pOPWtXxvloilUNM992EVFyiJTTg9yniXcZSsU=',
          'sp_private_key' => 'MIIEwQIBADANBgkqhkiG9w0BAQEFAASCBKswggSnAgEAAoIBAgDDZOeQF9Cp5k0WzNBye9S/3FgKxTZjcAPBFLtMMAhcx9+kLYMwS5J5h1OUKQcaoxmz/MiVKnrnozStdOKYIeS0C+8DmjRPjKEva77RYEy/Zu4l2Y+Nijt9/OMrO2JwuchHI9Xx+rqifDCR9rJ4vwbu/6/NhTVggSgsDsxlgGtLWC1zoUmwtcBe30t63P1eDrNAEg5EkM3y6OCsx6HaK7nAJmGaF6of/60UmEXB6qBVgZlU/qUmrVX89EdGvPrKWvYJcX3xAcIQh/on/1e/XmGMRYnBB6E0qyx6sL0ZmHzwH5jIUR5S1xwqWhSAjlOUHLSg2tYfHx0dn3UV2koY9QsKEQIDAQABAoIBAUs9S728rej+eajR7WJoNKA8pNpg3nSj6Y4sAYNw64dun7uEmwO51gleBt0Cf23OaFNaf5KQ7QrNWbeBTs/uHTcHcV4dvw7yxA6SmsPdJTB+3i1M/W4vUIFPI9q930YxA+IA9p1bQwrWb42FRWwhgvX9FyE4rjkfAu0UNbjQHoDAxNFHHW2OZm9DFtZE8Y3qFFLXjnwl2acFncexDbY0A9vVR+ldpTruz7LQXRmAhozXmVnRtzMlDWDB0hjUQJYIAuue6tTHuD6VcxGLKYUgfB4AZ8IkvD2cbky38omll3KvaPbtOJFGNsBaqt0PVrZv//iZQHgIZe2roKNGBpUNA/7BAoGBD0H/eeG8KoFjLyqLLlBDndHPb3aURJGqf/rFuooK29NBn5n30cftFv7mXc+RA9/SBbIrX5+0p6Nez4SuJLYfVHBzDF9YOVzQbDUWZZWIUtR0yBSl2WAFEET7jyofzXTKOCo2eFrnWj5a2Q0xEFj11f7q83pbdQ8HvbUdi+roaRCtAoGBDM5hrDc4CI8yR6La9fAtA4bkQ/SxiDwfh/Xnjg0eSiVIs5GeGCAJEIeNoZE7n9BQHhH5m0HyjHMnzMfLyexhW6/xHAktEvcEWZMBBIBTbXsGn/f4yKiyfLCsdoLtQIBBQTpYAXwbqVjE+L6xgK/noFdDV17XZcYbPk6xr+f6Hnd1AoGBCQi2z9/Mng5GP+MczatQnd1gyUqYt5DYNzawZKbfjxEixfFQPrH1u6vpkpoX7wdTP3QjIldZi/i7ZnvU8H+1RTXfqO+7ORuvfKJiRHupYAHTs7QmDvM/jEaL/FSgx/Hi2iaEYfbRDSnmeKXK6zcBOFfbnZZRGJpxpu3aNMI+IhdxAoGBC2RplWeGDG8+3mVs/k6L7NBKLn32lOhPcIb8V+0pnfIvC7el+XY+OhssjqeBcDlDnIyHDWwMVo92v4CZtOb48TTCfBtZor5mez0AMb3q+cDw8swI4JDaP3x33/G3F6NA6cL6WU/L18nlaBdUFtPlbUlT2dzAJ4Sl5bbh8UefxQylAoGBAKP0QllPVH3cAKcplu/vTm3Brw6cejhwUX21qBspHJDQcjbqQMqI4xdcY7oYwBGazgPKnBOgiRqSg4018dcJySL5tHneGuTXHVfp+4FznlOQKxRg7I6e/KUOzRSsLy49KlGs9OmuACe0MOTboDIn00mzUnxdmk4qsq34KaqJ4w5G',
          'sp_name_id_format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
          'map_users' => 0,
          'security_logout_requests_sign' => 0,
          'security_assertions_encrypt' => 0,
          'security_authn_requests_sign' => 0,
          'security_messages_sign' => NULL,
          'security_request_authn_context' => 1,
          'strict' => 1,
        ])
        ->save();
    }
  }

  /**
   * Tests configuration values were set correctly during installation.
   */
  public function testConfig() {
    $assert_session = $this->assertSession();

    // Assert that all install tasks have done what they should do.
    // @see lightning_install_tasks()
    $this->assertSame('/node', $this->config('system.site')->get('page.front'));
    $this->assertSame(UserInterface::REGISTER_ADMINISTRATORS_ONLY, $this->config('user.settings')->get('register'));
    $this->assertTrue(Role::load(Role::AUTHENTICATED_ID)->hasPermission('access shortcuts'));
    $theme_config = $this->config('system.theme');
    $this->assertSame('bartik', $theme_config->get('default'));
    $this->assertSame('claro', $theme_config->get('admin'));
    $this->assertTrue($this->config('node.settings')->get('use_admin_theme'));
    $theme_global = $this->config('system.theme.global');
    $this->assertStringContainsString('/lightning/lightning.png', $theme_global->get('logo.path'));
    $this->assertStringContainsString('/lightning/favicon.ico', $theme_global->get('favicon.path'));
    /** @var \Drupal\views\ViewEntityInterface $view */
    $view = View::load('frontpage');
    $this->assertInstanceOf(View::class, $view);
    $display = &$view->getDisplay('default');
    $this->assertTrue($display['display_options']['empty']['area_text_custom']['tokenize']);
    $this->assertStringContainsString('/lightning/README.md', $display['display_options']['empty']['area_text_custom']['content']);

    // lightning_core_update_8002() marks a couple of core view modes as
    // internal.
    $view_modes = EntityViewMode::loadMultiple([
      'node.rss',
      'node.search_index',
    ]);
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
   * Data provider for testModeratedContentTypes().
   *
   * @return array[]
   *   Sets of arguments to pass to the test method.
   */
  public function providerModeratedContentTypes() {
    return [
      ['page', 'page_creator'],
      ['page', 'administrator'],
      ['landing_page', 'landing_page_creator'],
      ['landing_page', 'administrator'],
    ];
  }

  /**
   * Tests that moderated content types do not show a Published checkbox.
   *
   * @param string $node_type
   *   The machine name of the content type to test.
   * @param string $role
   *   The machine name of the user role to log in with.
   *
   * @dataProvider providerModeratedContentTypes
   */
  public function testModeratedContentTypes($node_type, $role) {
    $assert_session = $this->assertSession();

    $account = $this->createUser();
    $account->addRole($role);
    $account->save();

    $this->drupalLogin($account);
    $this->drupalGet("/node/add/$node_type");
    $assert_session->statusCodeEquals(200);
    $assert_session->buttonExists('Save');
    $assert_session->fieldNotExists('status[value]');
    $assert_session->buttonNotExists('Save and publish');
    $assert_session->buttonNotExists('Save as unpublished');
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
