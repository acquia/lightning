<?php

namespace Drupal\lightning_media\PreviewHandler;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\lightning_media\PreviewHandlerBase;

/**
 * Preview handler for media bundles which require an embed code.
 */
class EmbedCode extends PreviewHandlerBase {

  /**
   * The entity view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * EmbedCodePreviewHandler constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The string translation service.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, TranslationInterface $translator) {
    parent::__construct($entity_manager, $translator);
    $this->viewBuilder = $entity_manager->getViewBuilder('media');
  }

  /**
   * {@inheritdoc}
   */
  public function alterForm(array &$form, FormStateInterface $form_state, EntityInterface $entity = NULL) {
    parent::alterForm($form, $form_state, $entity);

    $entity = $entity ?: $this->getEntity($form_state);
    $field = $this->getSourceField($entity)->getName();

    $form[$field]['widget'][0]['value']['#ajax'] = [
      'event' => 'change',
      'callback' => [$this, 'getPreviewContent'],
    ];
    $form['preview'] = [
      '#type' => 'container',
      'entity' => [
        '#markup' => '',
      ],
    ];

    $key = [$field, 0, 'value'];
    if ($form_state->hasValue($key)) {
      $entity->set($field, $form_state->getValue($key));
    }
    if ($entity->get($field)->value) {
      $form['preview']['entity'] = $this->viewBuilder->view($entity);
    }
  }

  /**
   * AJAX callback. Returns the commands for displaying a live preview.
   *
   * @param array $form
   *   The complete form.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The current form state.
   */
  public function getPreviewContent(array &$form) {
    $response = new AjaxResponse();

    $command = new HtmlCommand('#edit-preview', $form['preview']['entity']);
    $response->addCommand($command);

    return $response;
  }

}
