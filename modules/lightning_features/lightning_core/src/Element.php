<?php

namespace Drupal\lightning_core;

use Drupal\Core\Render\Element as RenderElement;

/**
 * Helpful functions for dealing with renderable arrays and elements.
 */
class Element {

  /**
   * Puts an associative array into an arbitrary order.
   *
   * @param array $values
   *   The array to reorder.
   * @param array $keys
   *   The keys, in their desired order.
   */
  public static function order(array &$values, array $keys) {
    $keys = array_values($keys);

    uksort($values, function ($a, $b) use ($keys) {
      return array_search($a, $keys) - array_search($b, $keys);
    });
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
    foreach (RenderElement::children($element) as $key) {
      if (is_array($element[$key])) {
        $element[$key] = call_user_func([static::class, __FUNCTION__], $element[$key]);
      }
    }
    return $element;
  }

  /**
   * Formats a set of strings with an Oxford comma.
   *
   * @param string[] $items
   *   The set of strings to format.
   * @param string $conjunction
   *   (optional) The translated conjunction to insert before the final item.
   *   Defaults to 'and'.
   *
   * @return string
   *   The single Oxford-ized string.
   */
  public static function oxford(array $items, $conjunction = 'and') {
    $count = count($items);

    if ($count < 2) {
      return (string) reset($items);
    }
    elseif ($count === 2) {
      return reset($items) . ' ' . $conjunction . ' ' . end($items);
    }
    else {
      $items[] = $conjunction . ' ' . array_pop($items);
      return implode(', ', $items);
    }
  }

  /**
   * Sets descriptions on child elements according to the #legend property.
   *
   * @param array $element
   *   The unprocessed element.
   *
   * @return array
   *   The processed element.
   */
  public static function processLegend(array $element) {
    if ($element['#legend']) {
      foreach (RenderElement::children($element) as $key) {
        if (is_callable($element['#legend'])) {
          $element[$key]['#description'] = $element['#legend']($element[$key]);
        }
        elseif (isset($element['#legend'][$key])) {
          $element[$key]['#description'] = $element['#legend'][$key];
        }
      }
    }
    return $element;
  }

}
