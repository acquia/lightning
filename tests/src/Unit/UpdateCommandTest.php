<?php

namespace Drupal\Tests\lightning\Unit;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ClassResolver;
use Drupal\lightning\Command\UpdateCommand;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @coversDefaultClass \Drupal\lightning\Command\UpdateCommand
 *
 * @group lightning
 */
class UpdateCommandTest extends UnitTestCase {

  /**
   * The mocked config containing the known versions.
   *
   * @var Config
   */
  protected $versions;

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

    $this->versions = $this->prophesize(Config::class);

    $config_factory = $this->prophesize(ConfigFactoryInterface::class);
    $config_factory->get('lightning.versions')->willReturn($this->versions->reveal());

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
      $config_factory->reveal(),
      $discovery->reveal()
    );
  }

  public function testProviderVersionIsKnown() {
    $this->versions->get()->willReturn([
      'fubar' => '1.2.2',
    ]);

    $definitions = $this->command->getDefinitions();
    $this->assertCount(1, $definitions);
    $this->assertArrayHasKey('fubar:1.2.3', $definitions);
  }

  public function testProviderVersionNotKnown() {
    $this->versions->get()->willReturn([]);

    $this->command->profileInfo = ['version' => '8.x-1.22'];

    $definitions = $this->command->getDefinitions();
    $this->assertCount(1, $definitions);
    $this->assertArrayHasKey('fubar:1.2.3', $definitions);
  }

  /**
   * @depends testProviderVersionIsKnown
   */
  public function testSinceWithKnownProviderVersion() {
    $this->versions->get()->willReturn([
      'fubar' => '1.2.2',
    ]);
    $this->command->since = '1.2.0';

    $definitions = $this->command->getDefinitions();
    $this->assertCount(3, $definitions);
    $this->assertArrayHasKey('fubar:1.2.1', $definitions);
    $this->assertArrayHasKey('fubar:1.2.2', $definitions);
    $this->assertArrayHasKey('fubar:1.2.3', $definitions);
  }

  /**
   * @depends testProviderVersionNotKnown
   */
  public function testSinceWithUnknownProviderVersion() {
    $this->versions->get()->willReturn([]);

    $this->command->since = '1.2.0';
    $this->command->profileInfo = ['version' => '8.x-1.22'];

    $definitions = $this->command->getDefinitions();
    $this->assertCount(3, $definitions);
    $this->assertArrayHasKey('fubar:1.2.1', $definitions);
    $this->assertArrayHasKey('fubar:1.2.2', $definitions);
    $this->assertArrayHasKey('fubar:1.2.3', $definitions);
  }

  /**
   * @depends testSinceWithKnownProviderVersion
   * @depends testSinceWithUnknownProviderVersion
   */
  public function testForce() {
    $input = $this->prophesize(InputInterface::class);
    $input->getOption('force')->willReturn(TRUE);

    $this->command->initialize($input->reveal(), $this->prophesize(OutputInterface::class)->reveal());
    $this->assertSame('0.0.0', $this->command->since);
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
  public $profileInfo;

  /**
   * {@inheritdoc}
   */
  public function initialize(InputInterface $input, OutputInterface $output) {
    parent::initialize($input, $output);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    return parent::getDefinitions();
  }

}
