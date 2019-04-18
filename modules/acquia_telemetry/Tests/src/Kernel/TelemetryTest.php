<?php

namespace Drupal\Tests\acquia_telemetry\Kernel;

use Drupal\acquia_telemetry\Telemetry;
use Drupal\KernelTests\KernelTestBase;

/**
 * @group lightning
 *
 * @coversDefaultClass \Drupal\acquia_lightning\Telemetry
 */
class TelemetryTest extends KernelTestBase {

  /**
   * @var \Drupal\acquia_telemetry\Telemetry
   */
  protected $telemetry;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->telemetry = $this->container->get('acquia.telemetry');
  }

  /**
   * @covers ::__construct
   */
  public function testTelemetryService() {
    $this->assertInstanceOf(Telemetry::class, $this->telemetry);
  }

  /**
   * @covers ::sendTelemetry
   */
  public function testSendTelemetry() {
    $prophecy = $this->prophesize(Telemetry::CLASS);
    $prophecy->sendTelemetry('Drupal cron ran', ['key' => 'value'])->willReturn(TRUE);
  }

}
