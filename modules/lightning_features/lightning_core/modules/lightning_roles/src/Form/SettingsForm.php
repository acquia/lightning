<?php

namespace Drupal\lightning_roles\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The settings form for controlling Lightning Roles' behavior.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * SettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The string translation service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, TranslationInterface $translator) {
    parent::__construct($config_factory);
    $this->setStringTranslation($translator);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['lightning_roles.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lightning_roles_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('lightning_roles.settings')->get('content_roles');

    $permission_map = function ($permission) {
      return str_replace('?', NULL, $permission);
    };

    foreach ($config as $key => $role) {
      $role['permissions'] = array_map($permission_map, $role['permissions']);

      $form['content_roles'][$key] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Provide @label role for new content types', [
          '@label' => $permission_map($role['label']),
        ]),
        '#default_value' => $role['enabled'],
        '#description' => $this->t('Gives permission to @permissions', [
          '@permissions' => implode(', ', $role['permissions']),
        ]),
      ];
    }
    $form['content_roles']['#tree'] = TRUE;

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('lightning_roles.settings');

    $roles = $config->get('content_roles');

    foreach ($form_state->getValue('content_roles') as $key => $value) {
      $roles[$key]['enabled'] = (boolean) $value;
    }

    $config->set('content_roles', $roles)->save();

    parent::submitForm($form, $form_state);
  }

}
