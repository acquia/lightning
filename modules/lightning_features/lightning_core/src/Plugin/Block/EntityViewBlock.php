<?php

namespace Drupal\lightning_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Drupal\lightning\FormHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A block that can display any entity.
 *
 * @Block(
 *   id = "entity_view",
 *   admin_label = @Translation("Entity View")
 * )
 */
class EntityViewBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The form helper.
   *
   * @var \Drupal\lightning\FormHelper
   */
  protected $formHelper;

  /**
   * EntityViewBlock constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\lightning\FormHelper $form_helper
   *   The form helper.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, FormHelper $form_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->formHelper = $form_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('lightning.form_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'entity_type' => NULL,
      'entity_id' => NULL,
      'view_mode' => 'default',
      'label_display' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search for the entity to display...'),
      '#attached' => [
        'library' => [
          'lightning_core/entity_search',
        ],
      ],
      '#attributes' => [
        'data-entity-search' => TRUE,
      ],
    ];
    if ($this->configuration['entity_type'] && $this->configuration['entity_id']) {
      $entity = $this->entityTypeManager
        ->getStorage($this->configuration['entity_type'])
        ->load($this->configuration['entity_id']);

      if ($entity) {
        $form['search']['#default_value'] = $entity->label();
      }
    }

    $form['entity_type'] = [
      '#type' => 'hidden',
      '#required' => TRUE,
      '#default_value' => $this->configuration['entity_type'],
    ];
    $form['entity_id'] = [
      '#type' => 'hidden',
      '#required' => TRUE,
      '#default_value' => $this->configuration['entity_id'],
    ];

    $form['view_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('View mode'),
      '#required' => TRUE,
      '#options' => [
        'default' => $this->t('Default'),
      ],
      '#id' => 'view-modes',
      '#default_value' => $this->configuration['view_mode'],
    ];

    // Run the normal process function(s) for radios...
    $this->formHelper->applyStandardProcessing($form['view_mode']);
    // ...bookended by our own special sauce.
    array_unshift($form['view_mode']['#process'], [$this, 'addViewModeOptions']);
    array_push($form['view_mode']['#process'], [$this, 'describeViewModeOptions']);

    $form['view_mode_update'] = [
      '#type' => 'button',
      '#value' => $this->t('Update'),
      '#ajax' => [
        'wrapper' => 'view-modes--wrapper',
        'callback' => [static::class, 'viewModeUpdate'],
      ],
      '#attributes' => [
        'class' => ['js-hide'],
      ],
      '#id' => 'view-mode-update',
    ];

    return $form;
  }

  /**
   * Creates a subform state object for the block settings.
   *
   * @param array $complete_form
   *   The complete block configuration form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The complete form state.
   *
   * @return \Drupal\Core\Form\SubformState
   *   The subform state.
   */
  protected function settingsState(array &$complete_form, FormStateInterface $form_state) {
    return SubformState::createForSubform($complete_form['settings'], $complete_form, $form_state);
  }

  /**
   * AJAX callback: returns the view mode selection element.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return array
   *   The view mode selection element.
   */
  public static function viewModeUpdate(array &$form, FormStateInterface $form_state) {
    return $form['settings']['view_mode'];
  }

  /**
   * Process callback: adds options to the view mode selection element.
   *
   * @param array $element
   *   The unprocessed element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The complete form state.
   * @param array $complete_form
   *   The complete form.
   *
   * @return array
   *   The processed element.
   */
  public function addViewModeOptions(array $element, FormStateInterface $form_state, array &$complete_form) {
    $entity_type = $this
      ->settingsState($complete_form, $form_state)
      ->getValue('entity_type');

    if ($entity_type) {
      /** @var \Drupal\Core\Entity\EntityViewModeInterface[] $view_modes */
      $view_modes = $this->entityTypeManager
        ->getStorage('entity_view_mode')
        ->loadByProperties([
          'targetEntityType' => $entity_type,
        ]);

      foreach ($view_modes as $view_mode) {
        $id = substr($view_mode->id(), strlen($entity_type) + 1);
        $element['#options'][$id] = $view_mode->label();
      }
    }

    return $element;
  }

  /**
   * Process callback: sets descriptions for all view mode options.
   *
   * @param array $element
   *   The unprocessed element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The complete form state.
   * @param array $complete_form
   *   The complete form.
   *
   * @return array
   *   The processed element.
   */
  public function describeViewModeOptions(array $element, FormStateInterface $form_state, array &$complete_form) {
    $entity_type = $this
      ->settingsState($complete_form, $form_state)
      ->getValue('entity_type');

    if ($entity_type) {
      foreach (Element::children($element) as $option) {
        /** @var \Drupal\Core\Entity\EntityViewModeInterface $view_mode */
        $view_mode = $this->entityTypeManager
          ->getStorage('entity_view_mode')
          ->load($entity_type . '.' . $option);

        if ($view_mode) {
          $element[$option]['#description'] = $view_mode->getThirdPartySetting('lightning_core', 'description');
        }
      }
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->configuration['entity_type'] = $form_state->getValue('entity_type');
    $this->configuration['entity_id'] = $form_state->getValue('entity_id');
    $this->configuration['view_mode'] = $form_state->getValue('view_mode');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $entity = $this->entityTypeManager
      ->getStorage($this->configuration['entity_type'])
      ->load($this->configuration['entity_id']);

    return $this->entityTypeManager
      ->getViewBuilder($this->configuration['entity_type'])
      ->view($entity, $this->configuration['view_mode']);
  }

}
