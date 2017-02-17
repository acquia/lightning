<?php

namespace Drupal\lightning_media\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget;

/**
 * Plugin implementation of the 'image_immutable' widget.
 *
 * This widget is identical to the image_image widget it extends, except that it
 * suppresses the Remove button and the link to the uploaded file(s).
 *
 * @FieldWidget(
 *   id = "image_immutable",
 *   label = @Translation("Immutable image"),
 *   field_types = {
 *     "image"
 *   }
 * )
 */
class ImmutableImage extends ImageWidget {

  /**
   * {@inheritdoc}
   */
  public static function process($element, FormStateInterface $form_state, $form) {
    $element = parent::process($element, $form_state, $form);

    foreach ($element['fids']['#value'] as $fid) {
      $element['file_' . $fid]['#access'] = FALSE;
    }
    $element['remove_button']['#access'] = FALSE;

    return $element;
  }

}
