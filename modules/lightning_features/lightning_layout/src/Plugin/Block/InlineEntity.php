<?php

namespace Drupal\lightning_layout\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Block(
 *   id = "inline_entity",
 *   admin_label = @Translation("Inline entity")
 * )
 */
class InlineEntity extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * InlineEntity constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
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
    $configuration = parent::defaultConfiguration();

    $configuration['entity_type'] = 'inline_block_content';
    $configuration['bundle'] = 'basic';

    return $configuration;
  }

  /**
   * @return \Drupal\Core\Entity\EntityInterface
   */
  protected function getEntity() {
    $configuration = $this->getConfiguration();

    $entity = isset($configuration['entity'])
      ? unserialize($configuration['entity'])
      : NULL;

    if ($entity) {
      // Inline blocks are not loadable, so their storage handler never sets
      // $entity->original. Which breaks the Entity API, and anything that uses
      // it (IEF, for example).
      $entity->original = $entity;
    }
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $form['entity']['#type'] = 'inline_entity_form';

    $entity = $this->getEntity();
    if ($entity) {
      $form['entity']['#entity_type'] = $entity->getEntityTypeId();
      $form['entity']['#bundle'] = $entity->bundle();
      $form['entity']['#default_value'] = $entity;
    }
    else {
      $configuration = $this->getConfiguration();
      $form['entity']['#entity_type'] = $configuration['entity_type'];
      $form['entity']['#bundle'] = $configuration['bundle'];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['#process'][] = [static::class, 'ensureSubmit'];

    return $form;
  }

  public static function ensureSubmit($form, $form_state, array &$complete_form) {
    // The submit button is a standard button, not a submit button, so it will
    // not trigger IEF. We need to ensure that it does.
    $complete_form['submit']['#executes_submit_callback'] = TRUE;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $configuration = $this->getConfiguration();
    $configuration['entity'] = serialize($form['entity']['#entity']);
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $entity = $this->getEntity();

    if ($entity) {
      $entity_type = $entity->getEntityTypeId();

      return $this->entityTypeManager
        ->getViewBuilder($entity_type)
        ->view($entity);
    }
    else {
      return [];
    }
  }

}
