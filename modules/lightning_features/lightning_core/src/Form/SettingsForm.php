<?php

namespace Drupal\lightning_core\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The settings form for controlling Lightning Core's behavior.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The user role entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $roleStorage;

  /**
   * The currently logged-in user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The config object being edited.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * SettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityStorageInterface $role_storage
   *   The user role entity storage handler.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The currently logged-in user.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   (optional) The string translation service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityStorageInterface $role_storage, AccountInterface $current_user, TranslationInterface $translator = NULL) {
    parent::__construct($config_factory);
    $this->roleStorage = $role_storage;
    $this->currentUser = $current_user;
    $this->config = $this->config('lightning_core.settings');
    $this->stringTranslation = $translator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')->getStorage('user_role'),
      $container->get('current_user'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['lightning_core.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lightning_core_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config->get('content_roles');

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
    $roles = $this->config->get('content_roles');

    foreach ($form_state->getValue('content_roles') as $key => $value) {
      $roles[$key]['enabled'] = (boolean) $value;
    }

    $this->config->set('content_roles', $roles)->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Allows access if the user has any administrative role.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Whether access is allowed.
   */
  public function access() {
    $roles = $this->currentUser->getRoles(TRUE);

    /** @var \Drupal\user\RoleInterface $role */
    foreach ($this->roleStorage->loadMultiple($roles) as $role) {
      if ($role->isAdmin()) {
        return AccessResult::allowed();
      }
    }
    return AccessResult::forbidden();
  }

}
