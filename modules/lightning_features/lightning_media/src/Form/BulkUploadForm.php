<?php

namespace Drupal\lightning_media\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\lightning_media\Exception\IndeterminateBundleException;
use Drupal\lightning_media\MediaHelper;
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
   * The media helper service.
   *
   * @var \Drupal\lightning_media\MediaHelper
   */
  protected $helper;

  /**
   * BulkUploadForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\lightning_media\MediaHelper $helper
   *   The media helper service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The string translation service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, MediaHelper $helper, TranslationInterface $translator) {
    $this->entityTypeManager = $entity_type_manager;
    $this->helper = $helper;
    $this->setStringTranslation($translator);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('lightning.media_helper'),
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
      '#extensions' => implode(' ', $this->helper->getFileExtensions(TRUE)),
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
    $bulk_create = [];

    $uploads = $form_state->getValue(['dropzone', 'uploaded_files']);

    foreach ($uploads as $upload) {
      // Create a file entity for the temporary file.
      /** @var \Drupal\file\FileInterface $file */
      $file = $this->entityTypeManager->getStorage('file')->create([
        'uri' => $upload['path'],
      ]);
      $file->setTemporary();
      $file->save();

      try {
        $entity = $this->helper->createFromInput($file);
      }
      catch (IndeterminateBundleException $e) {
        drupal_set_message('error', (string) $e);
        continue;
      }

      $file = MediaHelper::useFile($entity, $file);
      $file->setPermanent();
      $file->save();
      $entity->save();
      array_push($bulk_create, $bulk_create ? $entity->id() : $entity);
    }

    if ($bulk_create) {
      /** @var \Drupal\media_entity\MediaInterface $entity */
      $redirect = array_shift($bulk_create)->toUrl('edit-form', [
        'query' => [
          'bulk_create' => $bulk_create,
        ],
      ]);
      $form_state->setRedirectUrl($redirect);
    }
  }

}
