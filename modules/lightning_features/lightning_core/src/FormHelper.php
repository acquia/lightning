<?php

namespace Drupal\lightning_core;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\ElementInfoManagerInterface;

/**
 * Provides helper methods for dealing with form elements.
 */
class FormHelper {

  /**
   * The element info manager.
   *
   * @var \Drupal\Core\Render\ElementInfoManagerInterface
   */
  protected $elementInfo;

  /**
   * FormHelper constructor.
   *
   * @param \Drupal\Core\Render\ElementInfoManagerInterface $element_info
   *   The element info manager.
   */
  public function __construct(ElementInfoManagerInterface $element_info) {
    $this->elementInfo = $element_info;
  }

  /**
   * Ensures that a form element will run its default process functions.
   *
   * @param array $element
   *   The form element.
   */
  public function applyDefaultProcessing(array &$element) {
    if (empty($element['#process'])) {
      $element_info = $this->elementInfo->getInfo($element['#type']);

      if (isset($element_info['#process'])) {
        $element['#process'] = $element_info['#process'];
      }
    }
  }

  /**
   * Pre-render function to disable all buttons in a renderable element.
   *
   * @param array $element
   *   The renderable element.
   *
   * @return array
   *   The renderable element with all buttons (at all levels) disabled.
   */
  public static function disableButtons(array $element) {
    if (isset($element['#type'])) {
      $element['#access'] = !in_array($element['#type'], [
        'button',
        'submit',
        'image_button',
      ]);
    }

    // Recurse into child elements.
    foreach (Element::children($element) as $key) {
      if (is_array($element[$key])) {
        $element[$key] = static::disableButtons($element[$key]);
      }
    }
    return $element;
  }

}
