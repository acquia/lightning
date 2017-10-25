<?php

namespace Drupal\lightning_media\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lightning_core\Form\BulkCreationEntityFormTrait;
use Drupal\lightning_media\MediaHelper as Helper;
use Drupal\media\MediaForm as BaseMediaForm;

/**
 * Adds dynamic preview support to the media entity form.
 */
class MediaForm extends BaseMediaForm {

  use BulkCreationEntityFormTrait;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\media\MediaInterface $entity */
    $entity = $this->getEntity();

    $field = Helper::getSourceField($entity);
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

      if (Helper::isPreviewable($entity)) {
        $widget['#ajax'] = [
          'callback' => [static::class, 'onChange'],
          'wrapper' => 'preview',
          'method' => 'html',
          'event' => 'change',
        ];
        $form['preview'] = [
          '#pre_render' => [
            [$this, 'renderPreview'],
          ],
          '#prefix' => '<div id="preview">',
          '#suffix' => '</div>',
        ];
      }
    }
    return $form;
  }

  /**
   * Pre-render callback for the preview element.
   *
   * You might wonder why this rinky-dink bit of logic cannot be done in
   * ::form(). The reason is that, under some circumstances, the renderable
   * preview element will contain unserializable dependencies (like such as the
   * database connection), which will produce a 500 error when trying to cache
   * the form for AJAX purposes.
   *
   * By putting this logic in a pre-render callback, we ensure that the
   * unserializable preview element will only exist during the rendering stage,
   * and thus never be serialized for caching.
   *
   * @param array $element
   *   The preview element.
   *
   * @return array
   *   The renderable preview element.
   */
  public function renderPreview(array $element) {
    $entity = $this->getEntity();
    return $element + Helper::getSourceField($entity)->view('default');
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

    return Helper::getSourceField($entity)->view('default');
  }

}
