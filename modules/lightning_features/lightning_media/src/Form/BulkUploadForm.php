<?php

namespace Drupal\lightning_media\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A form for uploading multiple media assets at once.
 */
class BulkUploadForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * BulkUploadForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The string translation service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, TranslationInterface $translator) {
    $this->entityTypeManager = $entity_type_manager;
    $this->setStringTranslation($translator);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bulk_upload_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['dropzone'] = [
      '#type' => 'dropzonejs',
      '#dropzone_description' => $this->t('Drag files here to upload them'),
    ];
    $form['continue'] = [
      '#type' => 'submit',
      '#value' => $this->t('Continue'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $uploads = $form_state->getValue(['dropzone', 'uploaded_files']);

    foreach ($uploads as $upload) {
      // Create a file entity around the temporary file.
      $file = $this->entityTypeManager->getStorage('file')->create([
        'uri' => $upload['path'],
      ]);
      // Determine the media bundle that applies.
      // Create a media entity and save it.
      // Add the entity to the bulk creation queue.
    }
  }

}
