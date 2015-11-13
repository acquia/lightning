<?php

/**
 * @file
 * Contains \Drupal\lightning_media\Plugin\views\row\EntityEmbed.
 */

namespace Drupal\lightning_media\Plugin\views\row;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\views\Plugin\views\row\EntityRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extends the generic entity row plugin, wrapping each row in a <drupal-entity>
 * element suitable for consumption by Entity Embed.
 *
 * @ViewsRow(
 *   id = "entity_embed",
 *   deriver = "\Drupal\lightning_media\Plugin\views\row\EntityEmbedDeriver"
 * )
 */
class EntityEmbed extends EntityRow {

  /**
   * Entity query for embed buttons.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $buttonQuery;

  /**
   * Storage handler for embed buttons.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $buttonStorage;

  /**
   * {@inheritdoc}
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Entity\Query\QueryInterface $button_query
   *   Entity query for embed buttons.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager, QueryInterface $button_query) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_manager, $language_manager);
    $this->buttonQuery = $button_query;
    $this->buttonStorage = $entity_manager->getStorage('embed_button');
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
      $container->get('language_manager'),
      $container->get('entity.query')->get('embed_button')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render($row) {
    $render = parent::render($row);
    $render['#theme_wrappers'] = [
      'drupal_entity' => [
        '#attributes' => [
          'data-align' => 'none',
          'data-embed-button' => $this->options['embed_button'],
          'data-entity-embed-display' => 'entity_reference:entity_reference_entity_view',
          'data-entity-embed-settings' => Json::encode([
            'view_mode' => $this->options['view_mode'],
          ]),
          'data-entity-id' => $row->_entity->id(),
          'data-entity-label' => $row->_entity->label(),
          'data-entity-type' => $this->getDerivativeId(),
          'data-entity-uuid' => $row->_entity->uuid(),
        ],
      ],
    ];
    return $render;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['embed_button']['default'] = NULL;
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $form['embed_button'] = [
      '#type' => 'select',
      '#title' => $this->t('Embed button'),
      '#default_value' => $this->options['embed_button'],
      '#required' => TRUE,
    ];
    $buttons = $this->buttonStorage->loadMultiple($this->buttonQuery->execute());
    foreach ($buttons as $button) {
      $form['embed_button']['#options'][$button->id()] = $button->label();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();

    if ($this->options['embed_button']) {
      $button = $this->buttonStorage->load($this->options['embed_button']);
      $dependencies[$button->getConfigDependencyKey()][] = $button->getConfigDependencyName();
    }
    return $dependencies;
  }

}
