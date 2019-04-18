<?php

namespace Drupal\lightning\Form;

use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for opting a Lightning installation into Acquia telemetry.
 *
 * This form is displayed during the Lightning install process. It provides
 * the opportunity for users to opt-in to enabling the Lightning Telemetry
 * module.
 */
final class TelemetryOptInForm extends FormBase {

  /**
   * The module installer service.
   *
   * @var ModuleInstallerInterface
   */
  private $moduleInstaller;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'acquia_telemetry_opt_in';
  }

  /**
   * Constructs a new TelemetryOptInForm.
   *
   * @param ModuleInstallerInterface $module_installer
   *   The module installer service.
   */
  public function __construct(ModuleInstallerInterface $module_installer) {
    $this->moduleInstaller = $module_installer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_installer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#title'] = "Telemetry opt-in";
    $form['allow_telemetry'] = [
      '#type' => 'checkbox',
      '#title' => t('Allow Lightning to send anonymous telemetry data to Acquia'),
      // @todo Revise and finalize language.
      '#description' => $this->t('This module sends anonymous data about Acquia product usage to Acquia for product development purposes. No private information will be gathered. Data will not be used for marketing and will not be sold to any third parties. Telemetry can be disabled at any time by uninstalling the acquia_telemetry module.'),
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
      '#button_type' => 'primary',
    ];

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
     $this->moduleInstaller->install(['acquia_telemetry']);
    }
  }

}
