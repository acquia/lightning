<?php

namespace Drupal\Tests\lightning_core\Unit\DisplayHelperTest;

use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\lightning_core\DisplayHelper;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\lightning_core\DisplayHelper
 *
 * @group lightning
 * @group lightning_core
 */
class DisplayHelperTest extends UnitTestCase {

  /**
   * @covers ::getNewComponents
   */
  public function testNewComponents() {
    /** @var \Drupal\Core\Entity\Display\EntityDisplayInterface|\Prophecy\Prophecy\ProphecyInterface $display */
    $display = $this->prophesize(EntityDisplayInterface::class);
    $display->getComponents()->willReturn([
      'foo' => [
        'type' => 'fubar',
      ],
      'bar' => [
        'type' => 'pastafazoul',
      ],
    ]);

    /** @var \Drupal\Core\Entity\Display\EntityDisplayInterface|\Prophecy\Prophecy\ProphecyInterface $original */
    $original = $this->prophesize(EntityDisplayInterface::class);
    $original->getComponents()->willReturn([
      'foo' => [
        'type' => 'fubar',
      ],
    ]);

    $display = $display->reveal();
    $display->original = $original->reveal();

    $helper = new DisplayHelper(
      $this->prophesize(QueryFactory::class)->reveal(),
      $this->prophesize(EntityFieldManagerInterface::class)->reveal()
    );

    $components = $helper->getNewComponents($display);
    $this->assertArrayHasKey('bar', $components);
    $this->assertArrayNotHasKey('foo', $components);
  }

}
