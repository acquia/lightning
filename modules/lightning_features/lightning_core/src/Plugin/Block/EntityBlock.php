<?php

namespace Drupal\lightning_core\Plugin\Block;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\ctools\Plugin\Block\EntityView;
use Drupal\lightning\FormHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A block that displays a single entity in any view mode, without a context.
 *
 * @Block(
 *   id = "entity_block",
 *   deriver = "\Drupal\lightning_core\Plugin\Block\EntityBlockDeriver"
 * )
 */
class EntityBlock extends EntityView {

  /**
   * The form helper.
   *
   * @var \Drupal\lightning\FormHelper
   */
  protected $formHelper;

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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager, FormHelper $form_helper) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_manager);
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
      $container->get('entity.manager'),
      $container->get('lightning.form_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      // The label is automatically generated if not set.
      'label' => NULL,
      // Seeing as how this block renders an entire entity, it's unlikely we'll
      // want to display the label by default.
      'label_display' => FALSE,
      'entity_id' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    // The label is automatically generated if not set.
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
  public function blockForm($form, FormStateInterface $form_state) {
    $entity_type = $this->getDerivativeId();

    $form['entity_id'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Search for a(n) @entity_type...', [
        '@entity_type' => $this->entityManager->getDefinition($entity_type)->getSingularLabel(),
      ]),
      '#target_type' => $entity_type,
      '#autocreate' => FALSE,
    ];
    if ($this->configuration['entity_id']) {
      $form['entity_id']['#default_value'] = $this->getEntity();
    }

    $form = parent::blockForm($form, $form_state);

    // Display the view modes as radio buttons so that we can add descriptions.
    $form['view_mode']['#type'] = 'radios';

    // Run the normal process function(s) for radios...
    $this->formHelper->applyStandardProcessing($form['view_mode']);
    // ...followed by our own special sauce.
    $form['view_mode']['#process'][] = [$this, 'processViewMode'];

    return $form;
  }

  /**
   * Process callback: filters out internal view modes and adds descriptions.
   *
   * @param array $element
   *   The unprocessed element.
   *
   * @return array
   *   The processed element.
   */
  public function processViewMode(array $element) {
    $children = Element::children($element);

    $load = array_map(
      function ($id) {
        return $this->getDerivativeId() . '.' . $id;
      },
      $children
    );
    $loaded = $this->entityManager
      ->getStorage('entity_view_mode')
      ->loadMultiple($load);

    // View modes are normally identified by ENTITY_TYPE.VIEW_MODE, but the
    // element children are just the VIEW_MODE part. To make things easier,
    // map the child keys to the loaded entities.
    /** @var \Drupal\Core\Entity\EntityViewModeInterface[] $view_modes */
    $view_modes = array_combine($children, $loaded);

    foreach ($view_modes as $id => $view_mode) {
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
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->configuration['entity_id'] = $form_state->getValue('entity_id');

    // Automatically generate the label if we don't have it.
    $label = $this->label();
    if (empty($label)) {
      $entity = $this->getEntity();

      $this->configuration['label'] = sprintf(
        '%s: %s',
        ucfirst($entity->getEntityType()->getSingularLabel()),
        $entity->label()
      );
    }
  }

  /**
   * Returns the entity being displayed by this block.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity being displayed.
   */
  protected function getEntity() {
    $entity_type = $this->getDerivativeId();

    return $this->entityManager
      ->getStorage($entity_type)
      ->load($this->configuration['entity_id']);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $entity = $this->getEntity();
    $entity_type = $entity->getEntityTypeId();

    return $this->entityManager
      ->getViewBuilder($entity_type)
      ->view($entity, $this->configuration['view_mode']);
  }

}
