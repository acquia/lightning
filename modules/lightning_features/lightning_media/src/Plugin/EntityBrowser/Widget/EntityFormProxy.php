<?php

namespace Drupal\lightning_media\Plugin\EntityBrowser\Widget;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity_browser\WidgetBase;
use Drupal\inline_entity_form\Element\InlineEntityForm;
use Drupal\lightning_media\BundleResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base class for EB widgets which wrap around an (inline) entity form.
 */
abstract class EntityFormProxy extends WidgetBase {

  /**
   * The media bundle resolver.
   *
   * @var BundleResolverInterface
   */
  protected $bundleResolver;

  /**
   * The currently logged in user.
   *
   * @var AccountInterface
   */
  protected $currentUser;

  /**
   * Returns the current input value, if any.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return mixed
   *   The input value, ready for further processing. Nothing will be done with
   *   the value if it's empty.
   */
  abstract protected function getInputValue(FormStateInterface $form_state);

  /**
   * EmbedCode constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param BundleResolverInterface $bundle_resolver
   *   The media bundle resolver.
   * @param AccountInterface $current_user
   *   The currently logged in user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EventDispatcherInterface $event_dispatcher, EntityManagerInterface $entity_manager, BundleResolverInterface $bundle_resolver, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher, $entity_manager);
    $this->bundleResolver = $bundle_resolver;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $bundle_resolver = $plugin_definition['bundle_resolver'];

    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('event_dispatcher'),
      $container->get('entity.manager'),
      $container->get('plugin.manager.lightning_media.bundle_resolver')->createInstance($bundle_resolver),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $additional_widget_parameters) {
    $form = array(
      'entity' => array(
        '#markup' => NULL,
      ),
      'ief_target' => array(
        '#type' => 'container',
        '#id' => 'ief-target',
        '#weight' => 10,
      ),
    );

    $input = $this->getInputValue($form_state);
    if ($input) {
      $entity = $this->generateEntity($input);
      if ($entity) {
        $form['entity'] = array(
          '#type' => 'inline_entity_form',
          '#entity_type' => $entity->getEntityTypeId(),
          '#bundle' => $entity->bundle(),
          '#default_value' => $entity,
          '#form_mode' => 'media_browser',
          '#process' => array(
            [InlineEntityForm::class, 'processEntityForm'],
            [$this, 'processEntityForm'],
          ),
        );
      }
    }

    return $form;
  }

  /**
   * Performs additional processing on the inline entity form.
   *
   * @param array $entity_form
   *   The processed inline entity form element.
   *
   * @return array
   *   The processed element.
   */
  public function processEntityForm(array $entity_form) {
    return $entity_form;
  }

  /**
   * AJAX callback. Returns the inline entity form.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return array
   *   The renderable inline entity form.
   */
  public function getEntityForm(array &$form, FormStateInterface $form_state) {
    return $form['widget']['entity'];
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array &$form, FormStateInterface $form_state) {
    parent::validate($form, $form_state);

    $input = $this->getInputValue($form_state);
    if (empty($input)) {
      $form_state->setError($form['widget'], 'No input provided!');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array &$element, array &$form, FormStateInterface $form_state) {
    // IEF will take care of creating the entity upon submission. All we need to
    // do is send it upstream to Entity Browser.
    $entity = $form['widget']['entity']['#entity'];
    $this->selectEntities([$entity], $form_state);
  }

  /**
   * Generates a media entity from an embed code.
   *
   * @param string $input
   *   The input value from which to generate the entity.
   *
   * @return \Drupal\media_entity\MediaInterface|null
   *   A new, unsaved media entity, or null if the input value could not be
   *   handled by any existing media bundles.
   */
  protected function generateEntity($input) {
    $bundle = $this->bundleResolver->getBundle($input);

    if ($bundle) {
      /** @var \Drupal\media_entity\MediaInterface $entity */
      $entity = $this->entityTypeManager->getStorage('media')->create([
        'bundle' => $bundle->id(),
        'uid' => $this->currentUser->id(),
        'status' => TRUE,
      ]);
      $type_config = $bundle->getTypeConfiguration();
      $entity->set($type_config['source_field'], $input);

      return $entity;
    }
  }

}
