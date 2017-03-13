<?php

namespace Drupal\lightning_media;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\media_entity\MediaInterface;

/**
 * Base class for preview handlers.
 */
abstract class PreviewHandlerBase implements PreviewHandlerInterface {

  use DependencySerializationTrait;
  use SourceFieldTrait;
  use StringTranslationTrait;

  /**
   * The media bundle storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $bundleStorage;

  /**
   * The storage handler for entity form displays.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $displayStorage;

  /**
   * PreviewHandlerBase constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   (optional) The string translation service.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager, TranslationInterface $translator = NULL) {
    $this->bundleStorage = $entity_manager->getStorage('media_bundle');
    $this->fieldStorage = $entity_manager->getStorage('field_config');
    $this->displayStorage = $entity_manager->getStorage('entity_form_display');
    $this->stringTranslation = $translator;
  }

  /**
   * {@inheritdoc}
   */
  public function extraFields($bundle) {
    $extra = array();

    if (is_string($bundle)) {
      $bundle = $this->bundleStorage->load($bundle);
    }

    if ($bundle instanceof EntityInterface) {
      $extra['media'][$bundle->id()]['form']['preview'] = [
        'label' => $this->t('Preview'),
        'description' => $this->t('A live preview of the @bundle.', [
          '@bundle' => $bundle->label(),
        ]),
        'weight' => 0,
      ];
    }

    return $extra;
  }

  /**
   * {@inheritdoc}
   */
  public function alterForm(array &$form, FormStateInterface $form_state, EntityInterface $entity = NULL) {
  }

  /**
   * Returns the media entity being manipulated by the form.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return \Drupal\media_entity\MediaInterface
   *   The form's media entity.
   */
  protected function getEntity(FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\EntityFormInterface $form */
    $form = $form_state->getFormObject();
    return $form->getEntity();
  }

  /**
   * Returns the display configuration for the form.
   *
   * @param \Drupal\media_entity\MediaInterface $entity
   *   The media entity whose form is being displayed.
   * @param string $display_mode
   *   (optional) The form display mode. Defaults to 'default'.
   *
   * @return \Drupal\Core\Entity\Display\EntityFormDisplayInterface
   *   The form's display configuration.
   */
  protected function getDisplay(MediaInterface $entity, $display_mode = NULL) {
    $id = $entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . ($display_mode ?: 'default');
    return $this->displayStorage->load($id);
  }

}
