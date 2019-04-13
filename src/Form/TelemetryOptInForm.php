<?php
/**
 * @file
 * Contains \Drupal\lightning\Form\TelemetryOptInForm.
 */
namespace Drupal\lightning\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TelemetryOptInForm.
 *
 * This form is displayed during the Lightning install process.
 *
 * @package Drupal\lightning\Form
 */
class TelemetryOptInForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'telemetry_opt_in';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#title'] = "Telemetry Opt-In";
    $form['allow_telemetry'] = array(
      '#type' => 'checkbox',
      '#title' => t('Allow Lightning to send anonymized telemetry data.'),
      '#description' => t('Telemetry will be anonymized and send to Acquia for product development purposes. Information will be used in compliance with GDPR and will never be shared with third parties.')
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('allow_telemetry')) {
      \Drupal::service('module_installer')->install(['lightning_telemetry']);
    }
  }

}
