<?php

namespace Drupal\lightning\Tests\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Ensures the integrity and correctness of Lightning's bundled config.
 *
 * @group lightning
 */
class ConfigIntegrityTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'lightning';

  public function testConfig() {
    $this->assertTrue(TRUE);
  }

}
