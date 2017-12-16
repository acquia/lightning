<?php

namespace Drupal\Tests\lightning\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\lightning\Command\VersionCommand;

/**
 * @group lightning
 *
 * @coversDefaultClass \Drupal\lightning\Command\VersionCommand
 */
class VersionCommandTest extends KernelTestBase {

  /**
   * @covers ::toSemanticVersion
   */
  public function testToSemanticVersion() {
    $drupal_versions = [
      '8.x-1.23' => '1.2.3',
      '8.x-1.203' => '1.2.3',
      '8.x-1.230' => '1.2.30',
      '8.x-1.23-dev' => '1.2.3-dev',
    ];

    foreach ($drupal_versions as $drupal_version => $expected_semantic_version) {
      $generated_semantic_version = VersionCommand::toSemanticVersion($drupal_version);
      $this->assertSame($expected_semantic_version, $generated_semantic_version);
    }
  }

}
