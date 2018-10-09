<?php

namespace Drupal\Tests\lightning\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * @group lightning
 */
class SubProfileTest extends BrowserTestBase {

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

  public function testSubProfile() {
    $this->assertSame('lightning_extender', $this->container->getParameter('install_profile'));

    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = $this->container->get('module_handler');
    $this->assertTrue($module_handler->moduleExists('devel'));
    $this->assertFalse($module_handler->moduleExists('lightning_search'));
  }

}
