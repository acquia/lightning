<?php

namespace Drupal\lightning_core;

use Drupal\Core\Render\Element as BaseElement;

/**
 * Contains utility methods for dealing with render elements and arrays.
 */
class Element extends BaseElement {

  /**
   * Puts an associative array into an arbitrary order.
   *
   * @param array $items
   *   The array to reorder.
   * @param array $keys
   *   The keys, in their desired order.
   */
  public static function order(array &$items, array $keys) {
    $keys = array_values($keys);

    uksort($items, function ($a, $b) use ($keys) {
      return array_search($a, $keys) - array_search($b, $keys);
    });
  }

  /**
   * Recursively merges arrays using the + method.
   *
   * Existing keys at all levels of $a, both numeric and associative, will
   * always be preserved. That's why I'm calling this a "Canadian" merge -- it
   * does not want to step on any toes.
   *
   * @param array $a
   *   The input array.
   * @param array $b
   *   The array to merge into $a.
   *
   * @return array
   *   The merged arrays.
   */
  public static function mergeCanadian(array $a, array $b) {
    $a += $b;
    foreach ($a as $k => $v) {
      if (is_array($v) && isset($b[$k]) && is_array($b[$k])) {
        $a[$k] = static::mergeCanadian($a[$k], $b[$k]);
      }
    }
    return $a;
  }

}
