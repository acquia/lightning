<?php

/**
 * @file
 * Contains \Drupal\lightning_layout\Form\LandingPageForm.
 */

namespace Drupal\lightning_layout\Form;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LandingPageForm extends FormBase {

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $pageStorage;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $pageVariantStorage;

  /**
   * LandingPageForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $page_storage
   *   Storage handler for page entities.
   * @param \Drupal\Core\Entity\EntityStorageInterface $page_variant_storage
   *   Storage handler for page_variant entities.
   * @param \Drupal\Core\StringTranslation\TranslationInterface|NULL $translator
   *   (optional) String translation service.
   */
  public function __construct(EntityStorageInterface $page_storage, EntityStorageInterface $page_variant_storage, TranslationInterface $translator = NULL) {
    $this->pageStorage = $page_storage;
    $this->pageVariantStorage = $page_variant_storage;
    $this->stringTranslation = $translator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('page'),
      $container->get('entity_type.manager')->getStorage('page_variant'),
      $container->get('string_translation')
    );
  }

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

    /** @var \Drupal\page_manager\PageInterface $page */
    $page = $this->pageStorage->create([
      'id' => preg_replace('/[^a-z0-9_]+/', '_', $path),
      'path' => '/' . $path,
      'label' => $form_state->getValue('title'),
    ]);
    $this->pageStorage->save($page);

    /** @var \Drupal\page_manager\PageVariantInterface $variant */
    $variant = $this->pageVariantStorage->create([
      'id' => $page->id(),
      'label' => $page->label(),
      'page' => $page->id(),
      'variant' => 'panels_variant',
      'variant_settings' => [
        'layout' => 'onecol',
        // Always use Panels IPE to edit the page's layout and content.
        'builder' => 'in_place_editor',
      ],
    ]);
    $this->pageVariantStorage->save($variant);

    $form_state->setRedirectUrl(Url::fromUserInput($page->getPath()));
  }

}
