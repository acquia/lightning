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
use Drupal\layout_plugin\Plugin\Layout\LayoutPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form for normal people to create landing pages.
 */
class LandingPageForm extends FormBase {

  /**
   * The page entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $pageStorage;

  /**
   * The page variant entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $pageVariantStorage;

  /**
   * The layout plugin manager.
   *
   * @var \Drupal\layout_plugin\Plugin\Layout\LayoutPluginManagerInterface
   */
  protected $layoutPluginManager;

  /**
   * LandingPageForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $page_storage
   *   Entity storage handler for pages.
   * @param \Drupal\Core\Entity\EntityStorageInterface $page_variant_storage
   *   Entity storage handler for page variants.
   * @param \Drupal\layout_plugin\Plugin\Layout\LayoutPluginManagerInterface $layout_plugin_manager
   *   The layout plugin manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   (optional) String translation service.
   */
  public function __construct(EntityStorageInterface $page_storage, EntityStorageInterface $page_variant_storage, LayoutPluginManagerInterface $layout_plugin_manager, TranslationInterface $translator = NULL) {
    $this->pageStorage = $page_storage;
    $this->pageVariantStorage = $page_variant_storage;
    $this->layoutPluginManager = $layout_plugin_manager;
    $this->stringTranslation = $translator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('page'),
      $container->get('entity_type.manager')->getStorage('page_variant'),
      $container->get('plugin.manager.layout_plugin'),
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
    $form['layout'] = [
      '#type' => 'select',
      '#title' => $this->t('Layout'),
      '#required' => TRUE,
      '#default_value' => $form_state->getValue('layout'),
      '#options' => $this->layoutPluginManager->getLayoutOptions(),
    ];
    $form['actions'] = [
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Create'),
      ],
      '#type' => 'actions',
    ];
    $form['#attached']['library'][] = 'lightning_layout/landing_page_form';

    return $form;
  }

  /**
   * Returns the normalized path of the landing page.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return string
   *   The path of the landing page, without a leading slash.
   */
  protected function getPath(FormStateInterface $form_state) {
    return ltrim(strtolower($form_state->getValue('path')), '/');
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $path = '/' . $this->getPath($form_state);

    $pages = $this->pageStorage->loadByProperties([
      'path' => $path,
    ]);
    if ($pages) {
      $message = $this->t('A landing page already exists at @path.', [
        '@path' => $path,
      ]);
      $form_state->setError($form['path'], $message);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $path = $this->getPath($form_state);

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
        'layout' => $form_state->getValue('layout'),
        // Always use Panels IPE to edit the page's layout and content.
        'builder' => 'ipe',
        // The user-facing title of the page.
        'page_title' => $page->label(),
      ],
    ]);
    $this->pageVariantStorage->save($variant);

    $form_state->setRedirectUrl(Url::fromUserInput($page->getPath()));
  }

}
