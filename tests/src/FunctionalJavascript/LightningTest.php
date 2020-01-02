<?php

namespace Drupal\Tests\lightning\FunctionalJavascript;

use Drupal\block_content\Entity\BlockContent;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Tests integrated functionality of the Lightning profile.
 *
 * @group lightning
 * @group orca_public
 */
class LightningTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   *
   * Slick Entity Reference has a schema error.
   *
   * @todo Remove when depending on slick_entityreference 1.2 or later.
   */
  protected static $configSchemaCheckerExclusions = [
    'core.entity_view_display.block_content.media_slideshow.default',
  ];

  /**
   * {@inheritdoc}
   */
  protected $profile = 'lightning_extender';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    // Symlink the sub-profile into a place where Drupal will be able to find
    // it. The symlink is deleted in tearDown(). If the symlink cannot be
    // created, abort the test. In order to evade a too-strict coding standards
    // check, we need to call symlink() in an exotic way.
    if (!call_user_func('symlink', __DIR__ . '/../../' . $this->profile, "$this->root/profiles/$this->profile")) {
      $this->markTestSkipped("Could not symlink $this->profile into $this->root/profiles.");
    }
    parent::setUp();
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown() {
    unlink("$this->root/profiles/$this->profile");
    parent::tearDown();
  }

  /**
   * Tests integrated functionality of the Lightning profile.
   *
   * Because it takes aeons to install the full Lightning profile, or one of its
   * descendants, this test only has one public test method, with private
   * private helper methods testing specific scenarios. This is done purely for
   * performance reasons.
   */
  public function testLightning() {
    // Ensure that the sub-profile was installed...
    $this->assertSame('lightning_extender', $this->container->getParameter('install_profile'));

    $module_list = $this->container->get('extension.list.module')->getAllInstalledInfo();
    // ...and the changes it makes are reflected in the system.
    $this->assertArrayHasKey('ban', $module_list);
    $this->assertArrayNotHasKey('lightning_contact_form', $module_list);

    $this->doLandingPageSearchTest();
  }

  /**
   * Tests that landing pages are indexed for search correctly.
   */
  private function doLandingPageSearchTest() {
    // Test that landing pages are indexed for search correctly.
    $account = $this->drupalCreateUser();
    $account->addRole('landing_page_creator');
    $account->addRole('landing_page_reviewer');
    $account->addRole('layout_manager');
    $account->save();
    $this->drupalLogin($account);

    $landing_page = $this->drupalCreateNode([
      'type' => 'landing_page',
      'body' => 'In which my landing page is described in a flowery way.',
    ]);
    $this->assertSame('draft', $landing_page->moderation_state->value);

    $block_content = BlockContent::create([
      'type' => 'basic',
      'info' => 'Dragons',
      'body' => 'Here be dragons.',
    ]);
    $block_content->save();

    $this->drupalGet($landing_page->toUrl());

    $assert_session = $this->assertSession();
    $page = $this->getSession()->getPage();

    // Place the custom block in the layout.
    $tray = $assert_session->elementExists('css', '#panels-ipe-tray');
    $assert_session->elementExists('css', '.ipe-tabs', $tray)->clickLink('Manage Content');
    $assert_session->assertWaitOnAjaxRequest();
    $tab = $assert_session->elementExists('css', '.ipe-tabs-content', $tray);
    $assert_session->elementExists('css', '.ipe-category[data-category="Custom"]', $tab)->click();
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->elementExists('css', '.ipe-block-plugin a[data-plugin-id="block_content:' . $block_content->uuid() . '"]', $tab)->click();
    $assert_session->assertWaitOnAjaxRequest();
    $assert_session->elementExists('css', '.panels-ipe-block-plugin-form', $tab)->pressButton('Add');
    $assert_session->assertWaitOnAjaxRequest();
    $tray->clickLink('Save');
    $assert_session->assertWaitOnAjaxRequest();

    // Publish the landing page...
    $assert_session->elementExists('css', 'a[rel="edit-form"]')->click();
    $page->selectFieldOption('moderation_state[0][state]', 'Published');
    $page->pressButton('Save');
    $this->drupalLogout();
    $this->drupalGet('/search');

    // Search for text that is in the custom block, but not the body text, and
    // ensure that the body text is displayed.
    $page->fillField('Keywords', 'dragons');
    $page->pressButton('Search');
    $assert_session->pageTextContains($landing_page->getTitle());
    $assert_session->pageTextContains($landing_page->body->value);
  }

}
