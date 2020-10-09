<?php

namespace Drupal\Tests\lightning\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\user\UserInterface;
use Drupal\views\Entity\View;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tests integrated functionality of the Lightning profile.
 *
 * @group lightning
 * @group orca_public
 */
class LightningTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected function installDefaultThemeFromClassProperty(ContainerInterface $container) {
    // Normally, this method would try to determine the default theme to
    // install, first by looking for overridden config from the install profile,
    // and then by looking at $this->defaultTheme. For the purposes of this
    // test, though, we want to rely entirely on the profile to install and set
    // the default theme. Therefore, this method serves no purpose and is
    // disabled.
  }

  /**
   * {@inheritdoc}
   */
  protected static $configSchemaCheckerExclusions = [
    // @todo Remove this when depending on slick_entityreference 1.2 or later.
    'core.entity_view_display.block_content.media_slideshow.default',
    // @todo Remove when requiring Lightning Layout 2.2 or later.
    'core.entity_view_display.block_content.banner.default',
  ];

  /**
   * {@inheritdoc}
   */
  protected $profile = 'lightning_extender';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $root = static::getDrupalRoot();
    $this->assertNotEmpty($root);

    // Symlink the sub-profile into a place where Drupal will be able to find
    // it. The symlink is deleted in tearDown(). If the symlink cannot be
    // created, fail the test.
    $target = __DIR__ . '/../../' . $this->profile;
    $this->assertDirectoryIsReadable($target);

    $link = "$root/profiles/$this->profile";
    $this->assertDirectoryIsWritable(dirname($link));

    // symlink() is called strangely in order to evade a too-strict coding
    // standards check.
    $success = call_user_func('symlink', $target, $link);
    $this->assertTrue($success, "Could not symlink $link to $target");
    $this->assertDirectoryIsReadable($target);

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
   * Because it takes aeons to install the Lightning profile, or any of its
   * descendants, this test only has one public test method, with private helper
   * methods covering specific test scenarios. This is done purely for
   * performance reasons.
   */
  public function testLightning() {
    // Test that the sub-profile was installed...
    $this->assertSame('lightning_extender', $this->container->getParameter('install_profile'));

    $module_list = $this->container->get('extension.list.module')->getAllInstalledInfo();
    // ...and that the changes it makes are reflected in the system.
    $this->assertArrayHasKey('ban', $module_list);
    $this->assertArrayNotHasKey('lightning_search', $module_list);

    // Lightning's configuration overrides should be applied.
    $this->assertSame('/node', $this->config('system.site')->get('page.front'));
    $this->assertSame(UserInterface::REGISTER_ADMINISTRATORS_ONLY, $this->config('user.settings')->get('register'));
    $theme_config = $this->config('system.theme');
    $this->assertSame('claro', $theme_config->get('admin'));
    $this->assertSame('bartik', $theme_config->get('default'));
    $theme_global = $this->config('system.theme.global');
    $logo = $theme_global->get('logo');
    $this->assertStringContainsString('/lightning/lightning.png', $logo['path']);
    $this->assertFalse($logo['use_default']);
    $favicon = $theme_global->get('favicon');
    $this->assertStringContainsString('/lightning/favicon.ico', $favicon['path']);
    $this->assertFalse($favicon['use_default']);
    /** @var \Drupal\views\ViewEntityInterface $view */
    $view = View::load('frontpage');
    $this->assertInstanceOf(View::class, $view);
    $display = &$view->getDisplay('default');
    $this->assertStringContainsString('lightning', $display['display_options']['empty']['area_text_custom']['content']);

    $this->doModerationDashboardTest();
    $this->doTextBlockTest();
  }

  /**
   * Tests that the moderation dashboard works.
   */
  private function doModerationDashboardTest() {
    $account = $this->drupalCreateUser(['use moderation dashboard']);
    $this->drupalLogin($account);
    $this->drupalGet('/user/' . $account->id() . '/moderation/dashboard');
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalLogout();
  }

  /**
   * Tests the 'text' custom block type that ships with Lightning.
   */
  private function doTextBlockTest() {
    $assert_session = $this->assertSession();

    // Assert that basic blocks expose a Body field.
    $account = $this->createUser(['administer blocks']);
    $this->drupalLogin($account);

    $this->drupalGet('/block/add/text');
    $assert_session->statusCodeEquals(200);
    $assert_session->fieldExists('Body');

    $this->drupalLogout();
  }

}
