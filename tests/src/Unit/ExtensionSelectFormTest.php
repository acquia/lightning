<?php

namespace Drupal\Tests\lightning\Unit;

use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Form\FormState;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\lightning\Extender;
use Drupal\lightning\Form\ExtensionSelectForm;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\lightning\Form\ExtensionSelectForm
 * @group lightning
 */
class ExtensionSelectFormTest extends UnitTestCase {

  /**
   * The extender info class.
   *
   * @var Extender|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $extender;

  /**
   * The mocked info parser.
   *
   * @var InfoParserInterface|\Prophecy\Prophecy\ObjectProphecy
   */
  protected $infoParser;

  /**
   * The form under test.
   *
   * @var ExtensionSelectForm
   */
  protected $form;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->extender = $this->prophesize(Extender::class);
    $this->infoParser = $this->prophesize(InfoParserInterface::class);

    $this->form = new ExtensionSelectForm(
      $this->extender->reveal(),
      '/path/to/drupal/root',
      $this->infoParser->reveal(),
      $this->prophesize(TranslationInterface::class)->reveal()
    );
  }

  /**
   * Tests form submission.
   *
   * @covers ::submitForm
   */
  public function testSubmitForm() {
    $form_state = new FormState();

    $form_state->setValue('modules', ['foo', 'baz']);
    $form_state->setValue('sub_components', [
      'foo' => ['fizzbin', 'pastafazoul'],
    ]);
    $this->extender->getExcludedComponents()->willReturn(['pastafazoul']);
    $this->extender->getModules()->willReturn([]);

    $form = [];
    $this->form->submitForm($form, $form_state);
    $this->assertEquals(['foo', 'baz', 'fizzbin'], $GLOBALS['install_state']['lightning']['modules']);
  }

}
