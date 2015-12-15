<?php

/**
 * @file
 * Contains \Drupal\lightning_layout\Form\LandingPageForm.
 */

namespace Drupal\lightning_layout\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\page_manager\Entity\Page;
use Drupal\page_manager\Entity\PageVariant;

class LandingPageForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'landing_page';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#required' => TRUE,
      '#default_value' => $form_state->getValue('title'),
    ];
    $form['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path'),
      '#required' => TRUE,
      '#default_value' => $form_state->getValue('path'),
    ];
    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Create'),
    ];
    $form['#attached']['library'][] = 'lightning_layout/landing_page_form';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $path = ltrim(strToLower($form_state->getValue('path')), '/');

    $page = Page::create([
      'id' => preg_replace('/[^a-z0-9_]+/', '_', $path),
      'path' => '/' . $path,
      'label' => $form_state->getValue('title'),
    ]);
    $page->save();

    PageVariant::create([
      'id' => $page->id(),
      'label' => $page->label(),
      'page' => $page->id(),
      'variant' => 'panels_variant',
      'variant_settings' => [
        'layout' => 'onecol',
        // Always use Panels IPE to edit the page's layout and content.
        'builder' => 'in_place_editor',
      ],
    ])->save();

    $form_state->setRedirectUrl(Url::fromUserInput($page->getPath()));
  }

}
