<?php

namespace Drupal\lightning_media\Plugin\EntityBrowser\Widget;

use Drupal\Core\Form\FormStateInterface;

/**
 * An Entity Browser widget for creating media entities from embed codes.
 *
 * @EntityBrowserWidget(
 *   id = "embed_code",
 *   label = @Translation("Embed Code"),
 *   description = @Translation("Allows creation of media entities from embed codes."),
 *   bundle_resolver = "embed_code"
 * )
 */
class EmbedCode extends EntityFormProxy {

  /**
   * {@inheritdoc}
   */
  protected function getInputValue(FormStateInterface $form_state) {
    return $form_state->getValue('embed_code');
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $additional_widget_parameters) {
    $form = parent::getForm($original_form, $form_state, $additional_widget_parameters);

    $form['embed_code'] = array(
      '#type' => 'textarea',
      '#placeholder' => $this->t('Enter a URL...'),
      '#attributes' => array(
        'class' => array('keyup-change'),
      ),
      '#ajax' => array(
        'event' => 'change',
        'wrapper' => $form['ief_target']['#id'],
        'method' => 'html',
        'callback' => [$this, 'getEntityForm'],
      ),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array &$form, FormStateInterface $form_state) {
    $input = $this->getInputValue($form_state);
    $bundle = $this->bundleResolver->getBundle($input);
    if (empty($bundle)) {
      $form_state->setError($form['widget']['embed_code'], 'This is not a valid URL or embed code.');
    }
  }

}
