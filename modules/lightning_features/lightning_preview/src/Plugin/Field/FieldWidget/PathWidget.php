<?php

namespace Drupal\lightning_preview\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lightning_preview\AliasHandler;
use Drupal\path\Plugin\Field\FieldWidget\PathWidget as BasePathWidget;

/**
 * A path widget that transparently prepends the active workspace machine name.
 */
class PathWidget extends BasePathWidget {

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as $delta => $value) {
      if ($value['alias']) {
        $values[$delta]['alias'] = AliasHandler::addPrefix($value['alias']);
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
    $element['alias']['#default_value'] = AliasHandler::stripPrefix($element['alias']['#default_value']);

    return $element;
  }

}
