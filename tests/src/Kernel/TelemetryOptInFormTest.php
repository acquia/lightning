<?php

namespace Drupal\Tests\lightning\Kernel;

use Drupal\Core\Form\FormState;
use Drupal\KernelTests\KernelTestBase;
use Drupal\lightning\Form\TelemetryOptInForm;

/**
 * @group lightning
 */
class TelemetryOptInFormTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system'];

  public function provider() {
    return [
      [TRUE],
      [FALSE],
    ];
  }

  /**
   * @dataProvider provider
   */
  public function test($enabled) {
    $module_handler = $this->container->get('module_handler');
    $this->assertFalse($module_handler->moduleExists('acquia_telemetry'));

    $form_state = new FormState();
    $form_state->setValue('allow_telemetry', $enabled);
    $this->container
      ->get('form_builder')
      ->submitForm(TelemetryOptInForm::class, $form_state);

    $this->assertSame($enabled, $module_handler->moduleExists('acquia_telemetry'));
  }

}
