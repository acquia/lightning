<?php

namespace Drupal\Tests\lightning\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\lightning\Update\Update405;

/**
 * @coversDefaultClass \Drupal\lightning\Update\Update405
 *
 * @group lightning
 */
class Update405Test extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system'];

  /**
   * @covers ::enableAutosaveForm
   */
  public function testEnableAutosaveForm() {
    /** @var \Drupal\Core\Extension\ModuleHandlerInterface $module_handler */
    $module_handler = $this->container->get('module_handler');
    $this->assertFalse($module_handler->moduleExists('autosave_form'));
    $this->assertFalse($module_handler->moduleExists('conflict'));

    Update405::create($this->container)->enableAutosaveForm();

    $module_handler = $this->container->get('module_handler');
    $this->assertTrue($module_handler->moduleExists('autosave_form'));
    $this->assertTrue($module_handler->moduleExists('conflict'));
  }

}
