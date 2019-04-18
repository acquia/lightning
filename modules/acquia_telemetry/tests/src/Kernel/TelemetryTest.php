<?php

namespace Drupal\Tests\acquia_telemetry\Kernel;

use Drupal\KernelTests\KernelTestBase;
use GuzzleHttp\ClientInterface;
use Prophecy\Argument;

/**
 * @group lightning
 * @group acquia_telemetry
 *
 * @coversDefaultClass \Drupal\acquia_telemetry\Telemetry
 */
class TelemetryTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['acquia_telemetry', 'system'];

  public function testErrorSuppression() {
    $http_client = $this->prophesize(ClientInterface::class);
    $http_client->request(Argument::cetera())->willThrow('Exception');
    $this->container->set('http_client', $http_client->reveal());

    $telemetry = $this->container->get('acquia.telemetry');
    $this->assertFalse($telemetry->sendTelemetry('Foobaz'));

    $this->container->get('state')->set('acquia_telemetry.loud', TRUE);
    $this->setExpectedException('Exception');
    $telemetry->sendTelemetry('Blow up real good!');
  }

}
