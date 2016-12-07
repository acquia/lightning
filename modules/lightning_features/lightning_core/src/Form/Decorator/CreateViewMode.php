<?php

namespace Drupal\lightning_core\Form\Decorator;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\lightning_core\FormDecoratorInterface;

/**
 * Enables the creation of view modes from the Manage Display page.
 */
class CreateViewMode implements FormDecoratorInterface {

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
   * A view mode entity query.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $query;

  /**
   * CreateViewMode constructor.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The translation service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(TranslationInterface $translator, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager) {
    $this->stringTranslation = $translator;
    $this->routeMatch = $route_match;
    $this->viewModeStorage = $entity_type_manager->getStorage('entity_view_mode');
    $this->query = $this->viewModeStorage->getQuery()->count();
  }

  /**
   * Builds the elements for creating a view mode.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function addElements(array &$form, FormStateInterface $form_state) {
    if (isset($form['modes'])) {
      $form['modes']['new_label'] = [
        '#type' => 'textfield',
        '#placeholder' => $this->t('Create a new view mode...'),
      ];
      $form['modes']['new_id'] = [
        '#type' => 'machine_name',
        '#required' => FALSE,
        '#machine_name' => [
          'exists' => [$this, 'exists'],
          'source' => ['modes', 'new_label'],
        ],
      ];
      $form['modes']['submit_new'] = [
        '#type' => 'submit',
        '#value' => $this->t('Create'),
        '#submit' => [
          [$this, 'onSubmit'],
        ],
      ];
    }
  }

  /**
   * Generates a view mode ID.
   *
   * @param string $id
   *   The desired ID.
   *
   * @return string
   *   A view mode ID.
   */
  protected function deriveId($id) {
    // View mode IDs are always prefixed with the target entity type ID.
    return $this->routeMatch->getParameter('entity_type_id') . '.' . $id;
  }

  /**
   * Checks if a view mode exists.
   *
   * @param string $id
   *   The desired view mode ID.
   *
   * @return bool
   *   TRUE if the view mode exists, FALSE otherwise.
   */
  public function exists($id) {
    return $id
      ? (bool) $this->query->condition('id', $this->deriveId($id))->execute()
      : FALSE;
  }

  /**
   * Creates a view mode.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function onSubmit(array &$form, FormStateInterface $form_state) {
    $id = $form_state->getValue('new_id');

    list ($entity_type) = explode('.', $this->deriveId($id));

    $this->viewModeStorage
      ->create([
        'id' => $id,
        'label' => $form_state->getValue('new_label'),
        'targetEntityType' => $entity_type,
      ])
      ->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getDecoratedForms() {
    return [
      'entity_view_display_edit_form' => 'addElements',
    ];
  }

}
