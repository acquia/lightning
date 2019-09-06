<?php

namespace Drupal\Tests\lightning\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\lightning\Update\Update405;

/**
 * @group lightning
 */
class Update405Test extends KernelTestBase
{
    protected static $modules = array('system');

    /**
     */
  public function testEnableAutosaveForm() {

    $moduleHandler = $this->container->get('module_handler');

    $this->assertFalse($moduleHandler->moduleExists('autosave_form'));
    $this->assertFalse($moduleHandler->moduleExists('conflict'));

    $updateHandler = Update405::create($this->container);

    $updateHandler->enableAutosaveForm();

    $moduleHandler = $this->container->get('module_handler');

    $this->assertTrue($moduleHandler->moduleExists('autosave_form'));
    $this->assertTrue($moduleHandler->moduleExists('conflict'));
  }
}

