<?php

namespace Drupal\lightning_core\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
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
   * The view mode storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $viewModeStorage;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->viewModeStorage = $entity_type_manager->getStorage('entity_view_mode');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
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
      '#autocomplete_route_name' => 'view.viewable_content.rest',
      '#attached' => [
        'library' => [
          'lightning_core/entity_search',
        ],
      ],
      '#attributes' => [
        'data-entity-search' => TRUE,
      ],
    ];
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
      '#type' => 'select',
      '#title' => $this->t('View mode'),
      '#required' => TRUE,
      '#default_value' => $this->configuration['view_mode'],
      '#options' => [
        'default' => $this->t('Default'),
      ],
    ];

    return $form;
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
    return [];
  }

}
