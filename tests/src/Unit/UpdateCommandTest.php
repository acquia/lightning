<?php

namespace Drupal\Tests\lightning\Unit;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Core\DependencyInjection\ClassResolver;
use Drupal\lightning\Command\UpdateCommand;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\lightning\Command\UpdateCommand
 *
 * @group lightning
 */
class UpdateCommandTest extends UnitTestCase {

  /**
   * The command object under test.
   *
   * @var UpdateCommand
   */
  protected $command;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $discovery = $this->prophesize(DiscoveryInterface::class);
    $discovery->getDefinitions()->willReturn([
      'fubar:1.2.1' => [
        'id' => '1.2.1',
        'provider' => 'fubar',
      ],
      'fubar:1.2.2' => [
        'id' => '1.2.2',
        'provider' => 'fubar',
      ],
      'fubar:1.2.3' => [
        'id' => '1.2.3',
        'provider' => 'fubar',
      ],
    ]);

    $this->command = new TestUpdateCommand(
      new ClassResolver(),
      new \ArrayIterator(),
      $discovery->reveal()
    );
  }

  public function testSince() {
    $this->command->since = '1.2.2';

    $definitions = $this->command->getDefinitions();
    $this->assertCount(1, $definitions);
    $this->assertArrayHasKey('fubar:1.2.3', $definitions);
  }

}

final class TestUpdateCommand extends UpdateCommand {

  /**
   * {@inheritdoc}
   */
  public $since;

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    return parent::getDefinitions();
  }

}
