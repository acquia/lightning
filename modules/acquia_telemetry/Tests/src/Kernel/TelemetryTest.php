<?php

namespace Drupal\Tests\acquia_telemetry\Kernel;

use Drupal\acquia_telemetry\Telemetry;
use Drupal\KernelTests\KernelTestBase;

/**
 * @group lightning
 *
 * @coversDefaultClass \Drupal\acquia_telemetry\Telemetry
 */
class TelemetryTest extends KernelTestBase {

  /**
   * @var \Drupal\acquia_telemetry\Telemetry
   */
  protected $telemetry;

  /**
   * Tests that module is disabled by default.
   */
  public function testOptIn() {
    /** @var \Drupal\Core\Extension\ModuleHandler $module_handler */
    $module_handler = $this->container->get('module_handler');
    $this->assertFalse($module_handler->moduleExists('acquia_telemetry'));
  }

  /**
   * @covers ::__construct
   */
  public function testTelemetryService() {
    $this->container->get('module_installer')->install(['acquia_telemetry']);
    $this->telemetry = $this->container->get('acquia.telemetry');
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
