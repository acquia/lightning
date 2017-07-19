<?php

namespace Drupal\lightning_inline_block\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @Block(
 *   id = "inline_entity",
 *   admin_label = @Translation("Inline entity"),
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
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

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

    if (empty($configuration['entity'])) {
      throw new \InvalidArgumentException("$plugin_id block cannot be instantiated without a serialized entity");
    }
    else {
      $this->entity = unserialize($configuration['entity']);
    }
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
   * @return \Drupal\Core\Entity\EntityInterface
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $entity = $this->getEntity();

    $form['entity'] = [
      '#type' => 'inline_entity_form',
      '#entity_type' => $entity->getEntityTypeId(),
      '#bundle' => $entity->bundle(),
      '#default_value' => $entity,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['admin_label']['#access'] = FALSE;

    $form['#process'][] = [static::class, 'ensureSubmit'];

    return $form;
  }

  public static function ensureSubmit($form, FormStateInterface $form_state, array &$complete_form) {
    // The submit button is a standard button, not a submit button, so it will
    // not trigger IEF. We need to ensure that it does.
    $complete_form['submit']['#executes_submit_callback'] = TRUE;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $entity = $this->getEntity();
    return $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId())->view($entity);
  }

}
