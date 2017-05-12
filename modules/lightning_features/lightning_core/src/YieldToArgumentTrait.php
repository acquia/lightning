<?php

namespace Drupal\lightning_core;

use Drupal\Core\Form\FormStateInterface;

/**
 * Allows exposed an Views filter to disappear if an argument is present.
 */
trait YieldToArgumentTrait {

  /**
   * {@inheritdoc}
   */
  public function buildExposedForm(&$form, FormStateInterface $form_state) {
    parent::buildExposedForm($form, $form_state);

    if (empty($this->options['exposed'])) {
      return;
    }
    if (empty($this->options['expose']['argument'])) {
      return;
    }

    $argument = $this->options['expose']['argument'];
    $argument = $this->view->argument[$argument];
    /** @var \Drupal\views\Plugin\views\argument\ArgumentPluginBase $argument */
    $value = $argument->getValue();

    $key = $this->options['expose']['identifier'];
    $form[$key]['#access'] = is_null($value) || $argument->isException($value);
  }

  /**
   * {@inheritdoc}
   */
  public function buildExposeForm(&$form, FormStateInterface $form_state) {
    parent::buildExposeForm($form, $form_state);

    $form['expose']['argument'] = [
      '#type' => 'select',
      '#title' => $this->t('Yield to argument'),
      '#options' => [],
      '#empty_option' => $this->t('- None -'),
      '#default_value' => $this->options['expose']['argument'],
      '#description' => $this->t('If this argument has a non-null value (given or default), this filter will not be exposed to the user.'),
    ];
    /**
     * @var string $id
     * @var \Drupal\views\Plugin\views\argument\ArgumentPluginBase $argument
     */
    foreach ($this->displayHandler->getHandlers('argument') as $id => $argument) {
      $form['expose']['argument']['#options'][$id] = $argument->adminLabel();
      $form['expose']['argument']['#access'] = TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['expose']['contains']['argument']['default'] = NULL;

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultExposeOptions() {
    parent::defaultExposeOptions();
    $this->options['expose']['argument'] = NULL;
  }

}
