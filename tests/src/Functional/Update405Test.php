<?php

namespace Drupal\Tests\lightning\Functional;

use Drupal\KernelTests\KernelTestBase;
use Drupal\lightning\Update\Update405;
use Drupal\Tests\BrowserTestBase;

/**
 * @group lightning
 */
class Update405Test extends BrowserTestBase
{
  /**
   */
  public function testEnableAutosaveForm() {

    $moduleHandler = $this->container->get('module_handler');

    $this->assertFalse($moduleHandler->moduleExists('autosave_form'));
    $this->assertFalse($moduleHandler->moduleExists('conflict'));

    $updateHandler = Update405::create($this->container);

    $updateHandler->enableAutosaveForm();
  }
}
