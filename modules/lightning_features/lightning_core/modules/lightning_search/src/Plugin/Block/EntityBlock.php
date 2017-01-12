<?php

namespace Drupal\lightning_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Drupal\lightning\FormHelper;
use Drupal\lightning_core\Element as ElementHelper;
use Drupal\lightning_search\SearchHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A block that can display any entity in the content search index.
 *
 * @Block(
 *   id = "entity_search_block",
 *   admin_label = @Translation("Search for an entity"),
 *   category = @Translation("Lightning")
 * )
 */
class EntityBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
   * The search helper.
   *
   * @var \Drupal\lightning_search\SearchHelper
   */
  protected $searchHelper;

  /**
   * EntityBlock constructor.
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
   * @param \Drupal\lightning_search\SearchHelper $search_helper
   *   The search helper.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, FormHelper $form_helper, SearchHelper $search_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->formHelper = $form_helper;
    $this->searchHelper = $search_helper;
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
      $container->get('lightning.form_helper'),
      $container->get('lightning.search_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label' => NULL,
      'label_display' => NULL,
      'entity_type' => NULL,
      'entity_id' => NULL,
      'view_mode' => 'default',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    unset($form['label']['#required']);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->configuration['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search for an entity to display...'),
      '#attached' => [
        'library' => [
          'lightning_search/entity_search',
        ],
      ],
      '#attributes' => [
        'data-entity-search' => TRUE,
      ],
    ];
    $entity = $this->getEntity();
    if ($entity) {
      $form['search']['#default_value'] = $entity->label();
    }

    $supported = $this->searchHelper->getIndexedEntityTypes();
    foreach ($supported as $id => $entity_type) {
      $supported[$id] = $entity_type->getPluralLabel();
    }
    ElementHelper::oxford($supported, 'and', TRUE);
    $form['search']['#description'] = $this->t('@entity_types will be searched.', [
      '@entity_types' => implode(', ', $supported),
    ]);

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

    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface[] $displays */
    $displays = $this->entityTypeManager
      ->getStorage('entity_view_display')
      ->loadMultiple();

    if ($displays) {
      $form['view_mode'] = [
        '#type' => 'radios',
        '#title' => $this->t('View mode'),
        '#states' => [
          // You can only choose a view mode when the entity type is known.
          'visible' => [
            'input[name $= "settings[entity_type]"]' => [
              'empty' => FALSE,
            ],
          ],
        ],
      ];
      foreach ($displays as $display) {
        $value = $display->getTargetEntityTypeId() . '.' . $display->getMode();
        // The option label will be changed in ::processViewMode().
        $form['view_mode']['#options'][$value] = $this->t('Default');
      }
      if ($entity) {
        $form['view_mode']['#default_value'] = $entity->getEntityTypeId() . '.' . $this->configuration['view_mode'];
      }
      $this->formHelper->applyStandardProcessing($form['view_mode']);
      $form['view_mode']['#process'][] = [$this, 'processViewMode'];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->configuration['entity_type'] = $form_state->getValue('entity_type');
    $this->configuration['entity_id'] = $form_state->getValue('entity_id');

    if ($form_state->hasValue('view_mode')) {
      list (, $this->configuration['view_mode']) = explode('.', $form_state->getValue('view_mode'), 2);
    }

    $label = $form_state->getValue('label');
    if (empty($label)) {
      $entity = $this->getEntity();
      $this->configuration['label'] = $entity->getEntityType()->getLabel() . ': ' . $entity->label();
    }
  }

  /**
   * Process function for view mode selection element.
   *
   * Sets JavaScript visibility states and descriptions for view mode options,
   * and hides internal view modes.
   *
   * @param array $element
   *   The unprocessed element.
   *
   * @return array
   *    The processed element.
   */
  public function processViewMode(array $element) {
    $children = Element::children($element);

    // Each view mode should only be visible when the selected entity is of its
    // target type.
    foreach ($children as $id) {
      list ($entity_type) = explode('.', $id, 2);
      $element[$id]['#states']['visible']['input[name $= "settings[entity_type]"']['value'] = $entity_type;
    }

    /** @var \Drupal\Core\Entity\EntityViewModeInterface[] $view_modes */
    $view_modes = $this->entityTypeManager
      ->getStorage('entity_view_mode')
      ->loadMultiple($children);

    foreach ($view_modes as $id => $view_mode) {
      $element[$id]['#title'] = $view_mode->label();

      $settings = $view_mode->getThirdPartySettings('lightning_core');

      if (empty($settings['internal'])) {
        $element[$id]['#description'] = @$settings['description'];
      }
      else {
        $element[$id]['#access'] = FALSE;
      }
    }
    return $element;
  }

  /**
   * Returns the configured entity displayed by this block.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity, or NULL if one has not been configured yet.
   */
  protected function getEntity() {
    if ($this->configuration['entity_type'] && $this->configuration['entity_id']) {
      return $this->entityTypeManager
        ->getStorage($this->configuration['entity_type'])
        ->load($this->configuration['entity_id']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $entity = $this->getEntity();
    $entity_type = $entity->getEntityTypeId();

    return $this->entityTypeManager
      ->getViewBuilder($entity_type)
      ->view($entity, $this->configuration['view_mode']);
  }

}
