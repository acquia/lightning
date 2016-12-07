<?php

namespace Drupal\Tests\lightning_core\Unit;

use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\lightning_core\FormHelper;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\lightning_core\FormHelper
 */
class FormHelperTest extends UnitTestCase {

  /**
   * Tests applyDefaultProcessing().
   *
   * @covers ::applyDefaultProcessing
   */
  public function testApplyDefaultProcessing() {
    $element_info = $this->prophesize(ElementInfoManagerInterface::class);
    $element_info
      ->getInfo('pikachu')
      ->willReturn([
        '#process' => ['foo', 'baz'],
      ]);

    $helper = new FormHelper($element_info->reveal());

    // Elements with no #process array should get the defaults.
    $element = ['#type' => 'pikachu'];
    $helper->applyDefaultProcessing($element);
    $this->assertEquals(['foo', 'baz'], $element['#process']);

    // Elements with an existing #process array should be untouched.
    $element['#process'] = 'baz';
    $helper->applyDefaultProcessing($element);
    $this->assertEquals(['baz'], $element['#process']);
  }

}
