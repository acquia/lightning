<?php

/**
 * @file
 * Contains \Drupal\lightning_media\Controller\EmbedCodeController.
 */

namespace Drupal\lightning_media\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\AttachmentsResponseProcessorInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\media_entity\Entity\Media;
use Drupal\media_entity_embeddable_video\VideoProviderManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for the embed code media widget.
 */
class EmbedCodeController extends EntityCrudController {

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * The attachments processor for AJAX responses.
   *
   * @var \Drupal\Core\Render\AttachmentsResponseProcessorInterface
   */
  protected $attachmentsProcessor;

  /**
   * The video provider plugin manager.
   *
   * @var \Drupal\media_entity_embeddable_video\VideoProviderManager
   */
  protected $videoProviderManager;

  /**
   * EmbedCodeController constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The currently logged-in user.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typed_data_manager
   *   The typed data manager.
   * @param \Drupal\Core\Render\AttachmentsResponseProcessorInterface $attachments_processor
   *   The attachments processor for AJAX responses.
   * @param \Drupal\media_entity_embeddable_video\VideoProviderManager $video_provider_manager
   *   The video provider plugin manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user, RendererInterface $renderer, TypedDataManagerInterface $typed_data_manager, AttachmentsResponseProcessorInterface $attachments_processor, VideoProviderManager $video_provider_manager) {
    parent::__construct($entity_type_manager, $current_user, $renderer);
    $this->typedDataManager = $typed_data_manager;
    $this->attachmentsProcessor = $attachments_processor;
    $this->videoProviderManager = $video_provider_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('renderer'),
      $container->get('typed_data_manager'),
      $container->get('ajax_response.attachments_processor'),
      $container->get('plugin.manager.media_entity_embeddable_video.provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function post(Request $request) {
    $response = new JsonResponse();

    if ($request->request->has('embed_code')) {
      $embed_code = $request->request->get('embed_code');

      if ($provider = $this->isVideo($embed_code)) {
        $bundle = 'video';
      }
      elseif ($this->isTweet($embed_code)) {
        $bundle = 'tweet';
      }
      elseif ($this->isInstagram($embed_code)) {
        $bundle = 'instagram';
      }

      if (isset($bundle)) {
        $entity = Media::create([
          'bundle' => $bundle,
          'name' => 'TODO',
          'uid' => $this->currentUser()->id(),
          'status' => TRUE,
          'embed_code' => $embed_code,
        ]);
        $entity->save();

        $data = $this->getEntityResponseData($entity);

        // Add the rendered entity to the response.
        $build = $this->entityTypeManager()->getViewBuilder('media')->view($entity);
        $data['preview'] = $this->renderer->render($build);

        // If the rendering process attached any assets, include the requisite
        // AJAX commands in the response.
        if (isset($build['#attached'])) {
          $fake_response = new AjaxResponse();
          $fake_response->setAttachments($build['#attached']);
          $this->attachmentsProcessor->processAttachments($fake_response);
          $data['commands'] = $fake_response->getCommands();
        }

        // This is needed for Drupal.Ajax to trust the response.
        // @see \Drupal\Core\EventSubscriber\AjaxResponseSubscriber, line 110.
        $response->headers->set('X-Drupal-Ajax-Token', '1');
        $response->setData($data);
      }
    }

    return $response;
  }

  /**
   * Checks if a string is a valid video embed code (i.e., can be handled by
   * one of Media Entity Embeddable Video's providers).
   *
   * @param string $embed_code
   *   The embed code to check.
   *
   * @return \Drupal\media_entity_embeddable_video\VideoProviderInterface|false
   *   The video provider if $embed_code is a valid embed code; FALSE otherwise.
   */
  protected function isVideo($embed_code) {
    return $this->videoProviderManager->getProviderByEmbedCode($embed_code);
  }

  /**
   * Checks if a string is a valid Twitter embed code.
   *
   * @param string $embed_code
   *   The embed code to check.
   *
   * @return bool
   */
  protected function isTweet($embed_code) {
    return $this->validateStringAs($embed_code, 'TweetEmbedCode');
  }

  /**
   * Checks if a string is a valid Instagram embed code.
   *
   * @param string $embed_code
   *   The embed code to check.
   *
   * @return bool
   */
  protected function isInstagram($embed_code) {
    return $this->validateStringAs($embed_code, 'InstagramEmbedCode');
  }

  /**
   * Validates a string against a specific constraint.
   *
   * @param string $string
   *   The string to validate.
   * @param string $constraint
   *   The constraint's plugin ID.
   *
   * @return bool
   */
  protected function validateStringAs($string, $constraint) {
    $definition = $this->typedDataManager->createDataDefinition('string');
    $definition->addConstraint($constraint);
    $value = StringData::createInstance($definition);
    $value->setValue($string);
    return $value->validate()->count() == 0;
  }

  /**
   * {@inheritdoc}
   */
  public function put(EntityInterface $media) {
    $media->field_media_in_library = TRUE;
    $media->save();
    $data = $this->getEntityResponseData($media);
    return new JsonResponse($data);
  }

  /**
   * {@inheritdoc}
   */
  public function delete(EntityInterface $media) {
    $media->delete();
    return new JsonResponse();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityResponseData(EntityInterface $entity) {
    $data = parent::getEntityResponseData($entity);

    // Always render the thumbnail.
    $thumbnail = $entity->thumbnail->view();
    $thumbnail['#label_display'] = 'hidden';
    $data['thumbnail'] = $this->renderer->render($thumbnail);

    $preview = $this->entityTypeManager()
      ->getViewBuilder($entity->getEntityTypeId())
      ->view($entity);
    $data['preview'] = $this->renderer->render($preview);

    return $data;
  }

}
