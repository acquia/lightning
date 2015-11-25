<?php

/**
 * @file
 * Contains \Drupal\lightning_media\Controller\UploadController.
 */

namespace Drupal\lightning_media\Controller;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles uploads done directly from the CKEditor-integrated media library.
 */
class UploadController extends ControllerBase {

  /**
   * The file entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $fileStorage;

  /**
   * The media entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $mediaStorage;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The transliteration service.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected $transliteration;

  /**
   * The image factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * UploadController constructor.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $file_storage
   *   The file entity storage controller.
   * @param \Drupal\Core\Entity\EntityStorageInterface $media_storage
   *   The media entity storage controller.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The currently logged in user.
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   The transliteration service.
   * @param \Drupal\Core\Image\ImageFactory
   *   The image factory service.
   */
  public function __construct(EntityStorageInterface $file_storage, EntityStorageInterface $media_storage, RendererInterface $renderer, AccountInterface $current_user, TransliterationInterface $transliteration, ImageFactory $image_factory) {
    $this->fileStorage = $file_storage;
    $this->mediaStorage = $media_storage;
    $this->renderer = $renderer;
    $this->currentUser = $current_user;
    $this->transliteration = $transliteration;
    $this->imageFactory = $image_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('file'),
      $container->get('entity_type.manager')->getStorage('media'),
      $container->get('renderer'),
      $container->get('current_user'),
      $container->get('transliteration'),
      $container->get('image.factory')
    );
  }

  /**
   * Handles an uploaded file.
   *
   * The uploaded file is saved to the public files directory and wrapped by
   * a media entity.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   An object containing all the information needed to generate embed code
   *   for the media entity.
   */
  public function upload(Request $request) {
    $response = new JsonResponse();

    // Try to get the uploaded file from the request.
    $file = $request->files->get('file');

    if ($file instanceof UploadedFile) {
      // Derive the server-side file name by transliterating the file's original
      // client-side name.
      $uri = 'public://' . $this->transliteration->transliterate($file->getClientOriginalName());
      // Ensure that the uploaded file won't overwrite an existing one.
      $uri = file_destination($uri, FILE_EXISTS_RENAME);
      // Move the file directly into the public files directory.
      $file->move(PublicStream::basePath(), file_uri_target($uri));

      // Make the file "official" by creating an entity for it.
      /** @var \Drupal\file\FileInterface $file */
      $file = $this->fileStorage->create(['uri' => $uri]);
      $this->fileStorage->save($file);

      $image = $this->imageFactory->get($file->getFileUri());
      $thumbnail = [
        '#theme' => 'image',
        '#uri' => $file->getFileUri(),
        '#alt' => $file->getFilename(),
        '#width' => $image->getWidth(),
        '#height' => $image->getHeight(),
      ];

      $response_data = $this->getEntityResponseData($file);
      $response_data['thumbnail'] = $this->renderer->render($thumbnail);
      $response->setData($response_data);
    }
    return $response;
  }

  /**
   * "Cancels" an upload by deleting its associated file entity.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file entity to be deleted.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response (currently empty).
   */
  public function cancel(FileInterface $file) {
    $file->delete();
    // No need to return anything in the response: the server will produce a
    // 500 error if anything goes wrong when deleting the entity.
    return new JsonResponse();
  }

  /**
   * Saves an uploaded file entity as a media item.
   *
   * @param \Drupal\file\FileInterface $file
   *   The uploaded file entity.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The ID of the newly created media entity.
   */
  public function save(FileInterface $file) {
    /** @var \Drupal\media_entity\MediaInterface $media */
    $media = $this->mediaStorage->create([
      'bundle' => 'image',
      'name' => $file->getFilename(),
      'uid' => $file->getOwnerId(),
      'status' => TRUE,
      'thumbnail' => $file->id(),
      'field_media_in_library' => TRUE,
    ]);
    $this->mediaStorage->save($media);

    $response = $this->getEntityResponseData($media);
    $thumbnail = $media->thumbnail->view();
    $thumbnail['#label_display'] = 'hidden';
    $response['thumbnail'] = $this->renderer->render($thumbnail);

    return new JsonResponse($response);
  }

  protected function getEntityResponseData(EntityInterface $entity) {
    return [
      'entity_type' => $entity->getEntityTypeId(),
      'bundle' => $entity->bundle(),
      'id' => $entity->id(),
      'uuid' => $entity->uuid(),
      'label' => $entity->label(),
    ];
  }

}
