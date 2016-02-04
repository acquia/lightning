<?php

/**
 * @file
 * Contains \Drupal\lightning_media\Controller\UploadController.
 */

namespace Drupal\lightning_media\Controller;

use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StreamWrapper\PublicStream;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Handles uploads done directly from the CKEditor-integrated media library.
 */
class UploadController extends EntityCrudController {

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The currently logged-in user.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   The transliteration service.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The image factory service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user, RendererInterface $renderer, TransliterationInterface $transliteration, ImageFactory $image_factory) {
    parent::__construct($entity_type_manager, $current_user, $renderer);

    $this->fileStorage = $this->entityTypeManager()->getStorage('file');
    $this->mediaStorage = $this->entityTypeManager()->getStorage('media');
    $this->transliteration = $transliteration;
    $this->imageFactory = $image_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('renderer'),
      $container->get('transliteration'),
      $container->get('image.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function post(Request $request) {
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
   * {@inheritdoc}
   */
  public function delete(EntityInterface $file) {
    $file->delete();
    // No need to return anything in the response: the server will produce a
    // 500 error if anything goes wrong when deleting the entity.
    return new JsonResponse();
  }

  /**
   * {@inheritdoc}
   */
  public function put(EntityInterface $file) {
    /** @var \Drupal\media_entity\MediaInterface $media */
    $media = $this->mediaStorage->create([
      'bundle' => 'image',
      'name' => $file->getFilename(),
      'uid' => $file->getOwnerId(),
      'status' => TRUE,
      'thumbnail' => $file->id(),
      'image' => $file->id(),
      'field_media_in_library' => TRUE,
    ]);
    $this->mediaStorage->save($media);

    $response = $this->getEntityResponseData($media);

    $thumbnail = $media->thumbnail->view();
    $thumbnail['#label_display'] = 'hidden';
    $response['thumbnail'] = $this->renderer->render($thumbnail);

    return new JsonResponse($response);
  }

}
