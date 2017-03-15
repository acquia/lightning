<?php

namespace Drupal\lightning_media\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\media_entity\MediaForm as BaseMediaForm;
use Drupal\media_entity\MediaInterface;

/**
 * Adds dynamic preview support to the media entity form.
 */
class MediaForm extends BaseMediaForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\media_entity\MediaInterface $entity */
    $entity = $this->getEntity();

    $field = static::getSourceField($entity);
    if ($field) {
      // Get the source field widget element.
      $widget_keys = [
        $field->getName(),
        'widget',
        0,
        $field->first()->mainPropertyName(),
      ];
      $widget = &NestedArray::getValue($form, $widget_keys);

      // Add an attribute to identify it.
      $widget['#attributes']['data-source-field'] = TRUE;

      if (static::isPreviewable($entity)) {
        $widget['#ajax'] = [
          'callback' => [static::class, 'onChange'],
          'wrapper' => 'preview',
          'method' => 'html',
          'event' => 'change',
        ];

        $form['preview'] = $field->view('default');
        $form['preview']['#prefix'] = '<div id="preview">';
        $form['preview']['#suffix'] = '</div>';
      }
    }

    return $form;
  }

  /**
   * Indicates if the media entity's type plugin supports dynamic previews.
   *
   * @param \Drupal\media_entity\MediaInterface $entity
   *   The media entity.
   *
   * @return bool
   *   TRUE if dynamic previews are supported, FALSE otherwise.
   */
  public static function isPreviewable(MediaInterface $entity) {
    $plugin_definition = $entity->getType()->getPluginDefinition();

    return isset($plugin_definition['preview']);
  }

  /**
   * Returns the media entity's source field item list.
   *
   * @param \Drupal\media_entity\MediaInterface $entity
   *   The media entity.
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|null
   *   The media entity's source field item list, or NULL if the media type
   *   plugin does not define a source field.
   */
  public static function getSourceField(MediaInterface $entity) {
    $type_configuration = $entity->getType()->getConfiguration();

    return isset($type_configuration['source_field'])
      ? $entity->get($type_configuration['source_field'])
      : NULL;
  }

  /**
   * AJAX callback. Updates and renders the source field.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return array
   *   The renderable source field.
   */
  public static function onChange(array &$form, FormStateInterface $form_state) {
    /** @var static $handler */
    $handler = $form_state->getFormObject();
    $entity = $handler->getEntity();
    $handler->copyFormValuesToEntity($entity, $form, $form_state);

    return static::getSourceField($entity)->view('default');
  }

}
