<?php

namespace Drupal\lightning_core\Form\Decorator;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\lightning_core\FormDecoratorInterface;
use Drupal\lightning_core\FormHelper;

/**
 * Provides an arbitrary description field on various config entity forms.
 */
class ConfigEntityDescriptions implements FormDecoratorInterface {

  use DependencySerializationTrait;
  use StringTranslationTrait;

  /**
   * The cache tag invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $invalidator;

  /**
   * The form helper.
   *
   * @var \Drupal\lightning_core\FormHelper
   */
  protected $formHelper;

  /**
   * The user role storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $roleStorage;

  /**
   * ConfigEntityDescriptions constructor.
   *
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $invalidator
   *   The cache tag invalidator.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The string translation service.
   * @param \Drupal\lightning_core\FormHelper $form_helper
   *   The form helper.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(CacheTagsInvalidatorInterface $invalidator, TranslationInterface $translator, FormHelper $form_helper, EntityTypeManagerInterface $entity_type_manager) {
    $this->invalidator = $invalidator;
    $this->stringTranslation = $translator;
    $this->formHelper = $form_helper;
    $this->roleStorage = $entity_type_manager->getStorage('user_role');
  }

  /**
   * Adds a description field to an entity form.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function describeEntity(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $form_state->getFormObject()->getEntity();

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#description' => $this->t('Additional relevant information about this @entity_type, such as where it is used and what it is for.', [
        '@entity_type' => $entity->getEntityType()->getSingularLabel(),
      ]),
      '#rows' => 2,
      '#default_value' => $entity->getThirdPartySetting('lightning_core', 'description'),
    ];

    $form['actions']['submit']['#submit'][] = [$this, 'setDescription'];
  }

  /**
   * Saves an arbitrary description in an entity's third-party settings.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function setDescription(array &$form, FormStateInterface $form_state) {
    $form_state
      ->getFormObject()
      ->getEntity()
      ->setThirdPartySetting('lightning_core', 'description', $form_state->getValue('description'))
      ->save();

    // The help text block is very likely to be render cached, so invalidate the
    // relevant cache tag. See lightning_core_block_view_alter() and
    // lightning_core_help().
    $this->invalidator->invalidateTags(['block_view:help_block']);
  }

  /**
   * Alters the user profile form.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function alterUserForm(array &$form, FormStateInterface $form_state) {
    if (isset($form['account']['roles'])) {
      // Always run the default process functions before our special one.
      $this->formHelper->applyDefaultProcessing($form['account']['roles']);
      $form['account']['roles']['#process'][] = [$this, 'describeUserRoles'];
    }
  }

  /**
   * Adds descriptions to user role checkboxes.
   *
   * @param array $element
   *   The role checkboxes' form element.
   *
   * @return array
   *   The processed element.
   */
  public function describeUserRoles(array $element) {
    // Try to add a description for each individual role.
    foreach (Element::children($element) as $role) {
      // Don't overwrite any existing description.
      if (empty($element[$role]['#description'])) {
        $element[$role]['#description'] = $this->roleStorage
          ->load($role)
          ->getThirdPartySetting('lightning_core', 'description');
      }
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function getDecoratedForms() {
    return [
      'entity_view_mode_add_form' => 'describeEntity',
      'entity_view_mode_edit_form' => 'describeEntity',
      'entity_form_mode_add_form' => 'describeEntity',
      'entity_form_mode_edit_form' => 'describeEntity',
      'user_role_form' => 'describeEntity',
      'user_form' => 'alterUserForm',
    ];
  }

}
