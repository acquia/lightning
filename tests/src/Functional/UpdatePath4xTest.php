<?php

namespace Drupal\Tests\lightning\Functional;

use Drupal\FunctionalTests\Update\UpdatePathTestBase;
use Drush\TestTraits\DrushTestTrait;

/**
 * Tests the update path from Lightning 4.x.
 *
 * @group lightning
 */
class UpdatePath4xTest extends UpdatePathTestBase {

  use DrushTestTrait;

  /**
   * {@inheritdoc}
   */
  protected function setDatabaseDumpFiles() {
    $this->databaseDumpFiles = [
      __DIR__ . '/../../fixtures/4.1.0-beta1.php.gz',
    ];
  }

  /**
   * Tests updating from Lightning 4.1.0-beta1 via the UI.
   */
  public function test() {
    require_once __DIR__ . '/../../update.php';
    $this->getRandomGenerator()->image('public://star.png', '16x16', '16x16');
    $this->runUpdates();
    $this->drush('update:lightning', [], ['yes' => NULL]);
  }

}
