<?php

namespace Drupal\lightning_search\Plugin\Block;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\lightning\FormHelper;
use Drupal\lightning_core\Element as ElementHelper;
use Drupal\lightning_core\Plugin\Block\EntityBlock as BaseEntityBlock;
use Drupal\lightning_search\SearchHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A block that can display any entity in the content search index.
 *
 * @Block(
 *   id = "entity_search_block",
 *   admin_label = @Translation("Entity")
 * )
 */
class EntityBlock extends BaseEntityBlock {

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
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\lightning\FormHelper $form_helper
   *   The form helper.
   * @param \Drupal\lightning_search\SearchHelper $search_helper
   *   The search helper.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager, FormHelper $form_helper, SearchHelper $search_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_manager, $form_helper);
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
      $container->get('entity.manager'),
      $container->get('lightning.form_helper'),
      $container->get('lightning.search_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'entity_type' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
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

    $form['view_mode'] = [
      '#type' => 'radios',
      '#title' => $this->t('View mode'),
      '#states' => [
        'visible' => [
          'input[name $= "settings[entity_type]"]' => [
            'empty' => FALSE,
          ],
        ],
      ],
      '#options' => [
        'default' => $this->t('Default'),
      ],
    ];
    if ($this->configuration['view_mode'] == 'default') {
      $form['view_mode']['#default_value'] = $this->configuration['view_mode'];
    }
    elseif ($entity) {
      $form['view_mode']['#default_value'] = $entity->getEntityTypeId() . '.' . $this->configuration['view_mode'];
    }

    /** @var \Drupal\Core\Entity\EntityViewModeInterface[] $view_modes */
    $view_modes = $this->entityManager
      ->getStorage('entity_view_mode')
      ->loadMultiple();

    foreach ($view_modes as $id => $view_mode) {
      $form['view_mode']['#options'][$id] = $view_mode->label();
    }

    $this->formHelper->applyStandardProcessing($form['view_mode']);
    $form['view_mode']['#process'][] = [$this, 'processViewMode'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['entity_type'] = $form_state->getValue('entity_type');

    // Trim the target entity type ID off of the view mode ID.
    $view_mode = preg_replace('/^' . $this->configuration['entity_type'] . '\./', NULL, $form_state->getValue('view_mode'));
    $form_state->setValue('view_mode', $view_mode);

    parent::blockSubmit($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function processViewMode(array $element) {
    $children = Element::children($element);

    /** @var \Drupal\Core\Entity\EntityViewModeInterface[] $view_modes */
    $view_modes = $this->entityManager
      ->getStorage('entity_view_mode')
      ->loadMultiple($children);

    foreach ($view_modes as $id => $view_mode) {
      $settings = $view_mode->getThirdPartySettings('lightning_core');

      if (empty($settings['internal'])) {
        $element[$id]['#states']['visible']['input[name $= "settings[entity_type]']['value'] = $view_mode->getTargetType();
        $element[$id]['#description'] = @$settings['description'];
      }
      else {
        $element[$id]['#access'] = FALSE;
      }
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntity() {
    if ($this->configuration['entity_type'] && $this->configuration['entity_id']) {
      return $this->entityManager
        ->getStorage($this->configuration['entity_type'])
        ->load($this->configuration['entity_id']);
    }
  }

}
