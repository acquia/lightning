<?php

namespace Drupal\lightning_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
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
      // There is no default label; one will be automatically generated if not
      // set (see ::blockSubmit()).
      'label' => NULL,
      'entity_type' => NULL,
      'entity_id' => NULL,
      // The default view mode will be used unless otherwise specified. (See
      // ::build()).
      'view_mode' => NULL,
      // It's not too likely that we'll want to display the label by default,
      // since this block renders an entire entity.
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
      '#options' => [],
      '#default_value' => $this->configuration['view_mode'],
      '#states' => [
        'visible' => [
          'input[name$="settings[entity_type]"' => [
            'empty' => FALSE,
          ],
        ],
      ],
    ];
    /** @var \Drupal\Core\Entity\EntityViewModeInterface[] $all */
    $all = $this->entityTypeManager
      ->getStorage('entity_view_mode')
      ->loadMultiple();

    foreach ($all as $id => $view_mode) {
      // Exclude internal view modes.
      if ($view_mode->getThirdPartySetting('lightning_core', 'internal') == FALSE) {
        $form['view_mode']['#options'][$id] = $view_mode->label();
      }
    }

    // Run the normal process function(s) for radios...
    $this->formHelper->applyStandardProcessing($form['view_mode']);
    // ...followed by our own special sauce.
    array_push($form['view_mode']['#process'], [$this, 'describeViewModes']);

    return $form;
  }

  /**
   * Process callback: sets descriptions for all view mode options.
   *
   * @param array $element
   *   The unprocessed element.
   *
   * @return array
   *   The processed element.
   */
  public function describeViewModes(array $element) {
    $view_modes = Element::children($element);

    /** @var \Drupal\Core\Entity\EntityViewModeInterface[] $view_modes */
    $view_modes = $this->entityTypeManager
      ->getStorage('entity_view_mode')
      ->loadMultiple($view_modes);

    foreach ($view_modes as $id => $view_mode) {
      $element[$id]['#description'] = $view_mode->getThirdPartySetting('lightning_core', 'description');
      $element[$id]['#states']['visible']['input[name$="settings[entity_type]"]']['value'] = $view_mode->getTargetType();
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    // If left blank, a label is automatically generated. See ::blockSubmit().
    unset($form['label']['#required']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    // The default behavior is to return the admin_label as the label unless
    // otherwise specified. We don't want that.
    return $this->configuration['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->configuration['entity_type'] = $form_state->getValue('entity_type');
    $this->configuration['entity_id'] = $form_state->getValue('entity_id');
    $this->configuration['view_mode'] = $form_state->getValue('view_mode');

    $entity = $this->entityTypeManager
      ->getStorage($this->configuration['entity_type'])
      ->load($this->configuration['entity_id']);

    // Automatically generate a label if we don't have one.
    $label = $this->label();
    if (empty($label)) {
      $this->configuration['label'] = $entity->getEntityType()->getSingularLabel() . ': ' . $entity->label();
    }
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
      ->view($entity, $this->configuration['view_mode'] ?: 'default');
  }

}
