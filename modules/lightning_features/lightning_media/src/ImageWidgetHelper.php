<?php

namespace Drupal\lightning_media;

use Drupal\image\Plugin\Field\FieldWidget\ImageWidget;

/**
 * Contains helper functions for manipulating image field widgets.
 */
class ImageWidgetHelper {

  /**
   * Returns normalized Lightning Media-specific settings for the widget.
   *
   * @param \Drupal\image\Plugin\Field\FieldWidget\ImageWidget $widget
   *   The widget plugin.
   *
   * @return array
   *   The normalized settings.
   */
  protected static function getSettings(ImageWidget $widget) {
    $settings = $widget->getThirdPartySettings('lightning_media') ?: [];

    $settings += [
      'file_links' => TRUE,
      'remove_button' => TRUE,
    ];

    return $settings;
  }

  /**
   * Returns the form for an image widget's Lightning Media-specific settings.
   *
   * @param \Drupal\image\Plugin\Field\FieldWidget\ImageWidget $widget
   *   The widget plugin.
   *
   * @return array
   *   The settings form elements.
   */
  public static function getSettingsForm(ImageWidget $widget) {
    $settings = static::getSettings($widget);

    return [
      'file_links' => [
        '#type' => 'checkbox',
        '#title' => t('Show links to uploaded files'),
        '#default_value' => $settings['file_links'],
      ],
      'remove_button' => [
        '#type' => 'checkbox',
        '#title' => t('Show Remove button'),
        '#default_value' => $settings['remove_button'],
      ],
    ];
  }

  /**
   * Summarizes an image widget's Lightning Media-specific settings.
   *
   * @param \Drupal\image\Plugin\Field\FieldWidget\ImageWidget $widget
   *   The widget plugin.
   * @param array $summary
   *   (optional) An existing summary to augment.
   *
   * @return string[]
   *   The summarized settings.
   */
  public static function summarize(ImageWidget $widget, array &$summary = NULL) {
    $settings = static::getSettings($widget);

    if (is_null($summary)) {
      $summary = [];
    }

    if (empty($settings['file_links'])) {
      $summary[] = t('Do not link to uploaded files');
    }
    if (empty($settings['remove_button'])) {
      $summary[] = t('Hide Remove button');
    }

    return $summary;
  }

  /**
   * Alters an image widget form element.
   *
   * @param array $element
   *   The widget form element.
   * @param \Drupal\image\Plugin\Field\FieldWidget\ImageWidget $widget
   *   The widget plugin.
   */
  public static function alter(array &$element, ImageWidget $widget) {
    // Store the widget settings where process() can see them.
    $element['#settings'] = static::getSettings($widget);

    $element['#process'][] = [static::class, 'process'];
  }

  /**
   * Process callback: does extra processing of an image widget form element.
   *
   * @param array $element
   *   The form element.
   *
   * @return array
   *   The processed form element.
   */
  public static function process(array $element) {
    $settings = $element['#settings'];

    foreach ($element['fids']['#value'] as $fid) {
      $element['file_' . $fid]['#access'] = $settings['file_links'];
    }
    $element['remove_button']['#access'] = $settings['remove_button'];

    return $element;
  }

}
