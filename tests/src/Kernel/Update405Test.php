<?php

namespace Drupal\Tests\lightning\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\lightning\Update\Update405;

/**
* @group lightning
* @coversDefaultClass \Drupal\lightning\Update\Update405
*/
class Update405Test extends KernelTestBase
{
    protected static $modules = array('system');

  /**
   * @covers ::enableAutosaveForm
   */
  public function testEnableAutosaveForm() {

    $moduleHandler = $this->container->get('module_handler');

    $this->assertFalse($moduleHandler->moduleExists('autosave_form'));
    $this->assertFalse($moduleHandler->moduleExists('conflict'));

    Update405::create($this->container)->enableAutosaveForm();

    $moduleHandler = $this->container->get('module_handler');

    $this->assertTrue($moduleHandler->moduleExists('autosave_form'));
    $this->assertTrue($moduleHandler->moduleExists('conflict'));
  }
  /**
   * @covers ::enableRedirect
   */
  public function testEnableRedirect() {

    $moduleHandler = $this->container->get('module_handler');

    $this->assertFalse($moduleHandler->moduleExists('redirect'));

    Update405::create($this->container)->enableRedirect();

    $moduleHandler = $this->container->get('module_handler');

    $this->assertTrue($moduleHandler->moduleExists('redirect'));
  }
}

