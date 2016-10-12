<?php

namespace Drupal\lightning_preview\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\path\Plugin\Field\FieldWidget\PathWidget as BasePathWidget;

/**
 * Prepends user entered paths with the workspace.
 */
class PathWidget extends BasePathWidget {

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $delta => $value) {
      if ($value['alias']) {
        $values[$delta]['alias'] = static::prefixPath($value['alias']);
      }
    }
    return parent::massageFormValues($values, $form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // The prefixing needs to be totally transparent to the user, so strip the
    // workspace prefix out of the default value.
    $element['alias']['#default_value'] = static::unprefixPath($element['alias']['#default_value']);

    return $element;
  }

  /**
   * Strips a workspace name name out of a path.
   *
   * @param string $path
   *   The prefixed path.
   * @param string $prefix
   *   (optional) The workspace machine name. Defaults to the active workspace.
   *
   * @return string
   *   The unprefixed path.
   */
  public static function unprefixPath($path, $prefix = NULL) {
    if (empty($prefix)) {
      $prefix = \Drupal::service('workspace.manager')
        ->getActiveWorkspace()
        ->getMachineName();
    }

    // The live workspace never uses prefixing.
    if ($prefix == 'live') {
      return $path;
    }
    else {
      return preg_replace('/^\/' . $prefix . '\//', '/', $path);
    }
  }

  /**
   * Prepends a workspace machine name to a path.
   *
   * @param string $path
   *   The unprefixed path.
   * @param string $prefix
   *   (optional) The workspace machine name. Defaults to the active workspace.
   *
   * @return string
   *   The prefixed path.
   */
  public static function prefixPath($path, $prefix = NULL) {
    if (empty($prefix)) {
      $prefix = \Drupal::service('workspace.manager')
        ->getActiveWorkspace()
        ->getMachineName();
    }

    // The live workspace never uses prefixing.
    if ($prefix == 'live') {
      return $path;
    }

    $path = explode('/', ltrim($path, '/'));

    if ($path[0] != $prefix) {
      array_unshift($path, $prefix);
    }

    return '/' . implode('/', $path);
  }

}
