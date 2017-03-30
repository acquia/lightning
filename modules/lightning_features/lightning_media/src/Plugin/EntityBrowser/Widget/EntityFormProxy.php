<?php

namespace Drupal\lightning_media\Plugin\EntityBrowser\Widget;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_browser\WidgetBase;
use Drupal\inline_entity_form\ElementSubmit;
use Drupal\lightning_media\InputMatchInterface;
use Drupal\media_entity\MediaBundleInterface;

/**
 * Base class for EB widgets which wrap around an (inline) entity form.
 */
abstract class EntityFormProxy extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $additional_widget_parameters) {
    $form = parent::getForm($original_form, $form_state, $additional_widget_parameters);

    if (isset($form['actions'])) {
      $form['actions']['#weight'] = 100;
    }

    $form['entity'] = [
      '#markup' => NULL,
      '#prefix' => '<div id="entity">',
      '#suffix' => '</div>',
      '#weight' => 99,
    ];

    $input = $this->getInputValue($form_state);
    if ($input) {
      $entity = $this->generateEntity($input);
      if ($entity) {
        unset($form['entity']['#markup']);

        $form['entity'] += [
          '#type' => 'inline_entity_form',
          '#entity_type' => $entity->getEntityTypeId(),
          '#bundle' => $entity->bundle(),
          '#default_value' => $entity,
          '#form_mode' => $this->configuration['form_mode'],
        ];
        // Without this, IEF won't know where to hook into the widget.
        // Don't pass $original_form as the second argument to addCallback(),
        // because it's not just the entity browser part of the form, not the
        // actual complete form.
        ElementSubmit::addCallback($form['actions']['submit'], $form_state->getCompleteForm());
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function prepareEntities(array $form, FormStateInterface $form_state) {
    if (isset($form['widget']['entity']['#entity'])) {
      return [
        $form['widget']['entity']['#entity'],
      ];
    }
    else {
      return [];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array &$form, FormStateInterface $form_state) {
    $input = $this->getInputValue($form_state);
    $bundle = $this->getBundle($input);
    if (empty($bundle)) {
      $form_state->setError($form['widget'], $this->t('No media types can be matched to this input.'));
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
   * AJAX callback. Returns the rebuilt inline entity form.
   *
   * @param array $form
   *   The complete form.
   * @param FormStateInterface $form_state
   *   The current form state.
   *
   * @return AjaxResponse
   *   The AJAX response.
   */
  public static function ajax(array &$form, FormStateInterface $form_state) {
    return (new AjaxResponse)
      ->addCommand(
        new ReplaceCommand('#entity', $form['widget']['entity'])
      );
  }

  /**
   * Generates a media entity from an input value.
   *
   * @param mixed $input
   *   The input value from which to generate the entity.
   *
   * @return \Drupal\media_entity\MediaInterface|null
   *   A new, unsaved media entity, or null if the input value could not be
   *   matched to any existing media bundles.
   */
  protected function generateEntity($input) {
    $bundle = $this->getBundle($input);

    if ($bundle) {
      /** @var \Drupal\media_entity\MediaInterface $entity */
      $entity = $this->entityTypeManager->getStorage('media')->create([
        'bundle' => $bundle->id(),
      ]);
      $type_config = $bundle->getTypeConfiguration();
      $entity->set($type_config['source_field'], $input);

      return $entity;
    }
  }

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
  protected function getInputValue(FormStateInterface $form_state) {
    return $form_state->getValue('input');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = parent::defaultConfiguration();
    $configuration['form_mode'] = 'media_browser';
    return $configuration;
  }

  /**
   * Returns the first available media bundle that can handle an input value.
   *
   * @param mixed $input
   *   The input value.
   *
   * @return \Drupal\media_entity\MediaBundleInterface|false
   *   A media bundle which can handle the input, or FALSE if there are none.
   *
   * @deprecated and will be removed in Lightning 2.1.1.
   */
  protected function getBundle($input) {
    foreach ($this->getPossibleBundles() as $bundle) {
      $plugin = $bundle->getType();
      if ($plugin instanceof InputMatchInterface && $plugin->appliesTo($input, $bundle)) {
        return $bundle;
      }
    }
    return FALSE;
  }

  /**
   * Returns all available media bundles.
   *
   * @return \Drupal\media_entity\MediaBundleInterface[]
   *   All available media bundles for which the current user has create access.
   *
   * @deprecated and will be removed in Lightning 2.1.1.
   */
  protected function getPossibleBundles() {
    $access_handler = $this->entityTypeManager->getAccessControlHandler('media');

    return array_filter(
      $this->entityTypeManager
        ->getStorage('media_bundle')
        ->loadMultiple(),

      function (MediaBundleInterface $bundle) use ($access_handler) {
        return $access_handler->createAccess($bundle->id());
      }
    );
  }

}
