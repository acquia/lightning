<?php

namespace Drupal\Tests\lightning_core\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\lightning_core\Element;

/**
 * @coversDefaultClass \Drupal\lightning_core\Element
 *
 * @group lightning
 * @group lightning_core
 */
class ElementTest extends KernelTestBase {

  /**
   * @covers ::oxford
   */
  public function testOxford() {
    $this->assertEmpty(
      Element::oxford([])
    );
    $this->assertEquals(
      'fettucine',
      Element::oxford(['fettucine'])
    );
    $this->assertEquals(
      'fettucine and linguine',
      Element::oxford(['fettucine', 'linguine'])
    );
    $this->assertEquals(
      'fettucine, linguine, and ravioli',
      Element::oxford(['fettucine', 'linguine', 'ravioli'])
    );
  }

  /**
   * @covers ::processLegend
   */
  public function testProcessLegendArray() {
    $element = [
      'foo' => [
        '#type' => 'checkbox',
      ],
      'bar' => [
        '#type' => 'checkbox',
      ],
      'baz' => [
        '#type' => 'checkbox',
      ],
      '#legend' => [
        'foo' => 'Foo',
        'bar' => 'Bar',
        'baz' => 'Baz',
        'blorf' => 'Blorf',
      ],
    ];
    $element = Element::processLegend($element);
    $this->assertEquals('Foo', $element['foo']['#description']);
    $this->assertEquals('Bar', $element['bar']['#description']);
    $this->assertEquals('Baz', $element['baz']['#description']);
    $this->assertArrayNotHasKey('blorf', $element);
  }

  /**
   * @covers ::processLegend
   */
  public function testProcessLegendCallable() {
    $legend = function (array $item) {
      return ucfirst($item['#label']);
    };

    $element = [
      'foo' => [
        '#type' => 'checkbox',
        '#label' => 'foo',
      ],
      'bar' => [
        '#type' => 'checkbox',
        '#label' => 'bar',
      ],
      'baz' => [
        '#type' => 'checkbox',
        '#label' => 'baz',
      ],
      '#legend' => $legend,
    ];
    $element = Element::processLegend($element);
    $this->assertEquals('Foo', $element['foo']['#description']);
    $this->assertEquals('Bar', $element['bar']['#description']);
    $this->assertEquals('Baz', $element['baz']['#description']);
  }

}
