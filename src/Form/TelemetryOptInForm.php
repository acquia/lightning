<?php

namespace Drupal\lightning\Form;

use Drupal\Core\Extension\ModuleExtensionList;
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
   * The extension.list.module service.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleExtensionList;

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
   *   The module_installer service.
   * @param \Drupal\Core\Extension\ModuleExtensionList $module_extension_list
   *   The extension.list.module service.
   */
  public function __construct(ModuleInstallerInterface $module_installer, ModuleExtensionList $module_extension_list) {
    $this->moduleInstaller = $module_installer;
    $this->moduleExtensionList = $module_extension_list;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('module_installer'),
      $container->get('extension.list.module')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#title'] = "Telemetry opt-in";
    $form['allow_telemetry'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Install Acquia Telemetry module'),
      '#description' => $this->moduleExtensionList->get('acquia_telemetry')->info['description'],
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
