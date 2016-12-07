<?php

namespace Drupal\lightning_layout\Form\Decorator;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\lightning_core\FormDecoratorInterface;

/**
 * Tweaks the Panelizer UI for better usability.
 */
class PanelizerUiEnhancer implements FormDecoratorInterface {

  use DependencySerializationTrait;
  use StringTranslationTrait;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The view mode storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $viewModeStorage;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * PanelizerUiEnhancer constructor.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator.
   * @param \Drupal\Core\StringTranslation\TranslationInterface|null $translator
   *   The string translation service.
   */
  public function __construct(RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager, UrlGeneratorInterface $url_generator, TranslationInterface $translator = NULL) {
    $this->routeMatch = $route_match;
    $this->viewModeStorage = $entity_type_manager->getStorage('entity_view_mode');
    $this->urlGenerator = $url_generator;
    $this->stringTranslation = $translator;
  }

  /**
   * Alters the form.
   *
   * @param array $form
   *   The form.
   */
  public function alterForm(array &$form) {
    // Use a process function to ensure that we run after Panelizer.
    $form['#process'][] = [$this, 'processForm'];
  }

  /**
   * Performs additional processing for internal view modes.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return array
   *   The processed form.
   */
  public function processForm(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display */
    $display = $form_state->getFormObject()->getEntity();

    $is_panelized = $display->getThirdPartySetting('panelizer', 'enable');

    // Check if this display is for an internal view mode.
    $view_mode = $this->viewModeStorage
      ->load(
        $display->getTargetEntityTypeId() . '.' . $display->getMode()
      );

    if ($view_mode) {
      $internal = $view_mode->getThirdPartySetting('lightning_core', 'internal');
      if ($internal) {
        // If it's not already applied, don't allow Panelizer.
        $form['panelizer']['#access'] = $is_panelized;

        // Inform the user what's up.
        $message = $this->t('This display is internal and will not be seen by normal users.');
        drupal_set_message($message, 'warning');
      }
    }

    return $is_panelized ? $this->processPanelizerUi($form) : $form;
  }

  /**
   * Performs additional processing on Panelizer's UI elements.
   *
   * @param array $element
   *   The element containing the Panelizer UI elements.
   *
   * @return array
   *   The processed element.
   */
  protected function processPanelizerUi(array $element) {
    // Don't show the table caption.
    // TODO: Is there an accessible way to hide this?
    unset($element['panelizer']['displays']['#caption']);

    $route_parameters = array_filter(
      $this->routeMatch->getParameters()->all(),
      'is_scalar'
    );

    // We got rid of the local action for this, so jury-rig a new local action
    // that we can mix in with the rest of the UI elements.
    // See lightning_layout_menu_local_actions_alter().
    $element['panelizer']['add_link'] = [
      '#theme' => 'menu_local_action',
      '#link' => [
        'title' => t('Create a layout'),
        'url' => $this->urlGenerator->getPathFromRoute('panelizer.wizard.add', $route_parameters),
      ],
      // @TODO: Use a theme wrapper if possible.
      '#prefix' => '<ul class="action-links">',
      '#suffix' => '</ul>',
    ];
    array_reorder($element['panelizer'], [
      'enable',
      'options',
      'add_link',
      'displays',
    ]);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function getDecoratedForms() {
    return [
      'entity_view_display_edit_form' => 'alterForm',
    ];
  }

}
