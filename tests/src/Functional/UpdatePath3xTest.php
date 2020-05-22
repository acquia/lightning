<?php

namespace Drupal\Tests\lightning\Functional;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drush\TestTraits\DrushTestTrait;

/**
 * Tests the update path from Lightning 3.x.
 *
 * @group lightning
 */
class UpdatePath3xTest extends UpdatePathTestBase {

  use DrushTestTrait;

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../fixtures/3.4.0.php.gz',
    ];
  }

  /**
   * Tests updating from Lightning 3.4.0 via the UI.
   */
  public function test() {
    require_once __DIR__ . '/../../update.php';
    $this->getRandomGenerator()->image('public://star_2.png', '16x16', '16x16');
    $this->runUpdates();
    $this->drush('update:lightning', [], ['yes' => NULL]);
  }

}
