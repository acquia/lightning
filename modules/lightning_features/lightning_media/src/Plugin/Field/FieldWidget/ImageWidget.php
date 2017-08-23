<?php

namespace Drupal\lightning_media\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget as BaseImageWidget;

class ImageWidget extends BaseImageWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = parent::defaultSettings();

    // Show file links by default.
    $settings['file_links'] = TRUE;

    // Show the Remove button by default.
    $settings['remove_button'] = TRUE;

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Store the widget settings where process() can get them.
    $element['#settings'] = $this->getSettings();

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function process($element, FormStateInterface $form_state, $form) {
    $element = parent::process($element, $form_state, $form);

    // Get the settings stored by formElement().
    $settings = $element['#array_parents'];
    array_push($settings, '#settings');
    $settings = NestedArray::getValue($form, $settings);

    foreach ($element['fids']['#value'] as $fid) {
      $element['file_' . $fid]['#access'] = $settings['file_links'];
    }
    $element['remove_button']['#access'] = $settings['remove_button'];

    return $element;
  }

}
