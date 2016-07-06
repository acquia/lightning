<?php

namespace Drupal\lightning_media;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\video_embed_field\ProviderManagerInterface;

/**
 * Determines the media bundle which can handle an embed code.
 */
class MediaBundleResolver {

  /**
   * The media_bundle entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $bundleStorage;

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedData;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The video provider manager.
   *
   * @var \Drupal\video_embed_field\ProviderManagerInterface
   */
  protected $videoProviders;

  /**
   * MediaBundleResolver constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typed_data_manager
   *   The typed data manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\video_embed_field\ProviderManagerInterface $video_providers
   *   (optional) The video provider manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, TypedDataManagerInterface $typed_data_manager, ModuleHandlerInterface $module_handler, ProviderManagerInterface $video_providers = NULL) {
    $this->bundleStorage = $entity_type_manager->getStorage('media_bundle');
    $this->typedData = $typed_data_manager;
    $this->moduleHandler = $module_handler;
    $this->videoProviders = $video_providers;
  }

  /**
   * Determines the media bundle that can handle a specific embed code.
   *
   * @param string $embed_code
   *   The embed code.
   *
   * @return \Drupal\media_entity\MediaBundleInterface|false
   *   The matching bundle, or false if there isn't one.
   */
  public function getBundleFromEmbedCode($embed_code) {
    switch (TRUE) {
      case $this->isVideo($embed_code):
        return $this->bundleStorage->load('video');

      case $this->isTweet($embed_code):
        return $this->bundleStorage->load('tweet');

      case $this->isInstagram($embed_code):
        return $this->bundleStorage->load('instagram');

      default:
        return FALSE;
    }
  }

  /**
   * Checks if an embed code is a video.
   *
   * @param string $embed_code
   *   The embed code.
   *
   * @return bool
   *   TRUE if the embed code is for a supported video provider, FALSE
   *   otherwise.
   */
  public function isVideo($embed_code) {
    if ($this->videoProviders) {
      return (boolean) $this->videoProviders->loadProviderFromInput($embed_code);
    }
    else {
      return FALSE;
    }
  }

  /**
   * Checks if an embed code is a tweet.
   *
   * @param string $embed_code
   *   The embed code.
   *
   * @return bool
   *   TRUE if the embed code is a tweet, FALSE otherwise.
   */
  public function isTweet($embed_code) {
    if ($this->moduleHandler->moduleExists('media_entity_twitter')) {
      return $this->validateStringAs('TweetEmbedCode', $embed_code);
    }
    else {
      return FALSE;
    }
  }

  /**
   * Checks if an embed code is an Instagram post.
   *
   * @param string $embed_code
   *   The embed code.
   *
   * @return bool
   *   TRUE if the embed code is an Instagram post, FALSE otherwise.
   */
  public function isInstagram($embed_code) {
    if ($this->moduleHandler->moduleExists('media_entity_instagram')) {
      return $this->validateStringAs('InstagramEmbedCode', $embed_code);
    }
    else {
      return FALSE;
    }
  }

  /**
   * Validates a string against an arbitrary constraint.
   *
   * @param string $constraint
   *   The constraint ID.
   * @param string $input
   *   The string to validate.
   *
   * @return bool
   *   TRUE if the input validates, FALSE otherwise.
   */
  protected function validateStringAs($constraint, $input) {
    $definition = $this->typedData->createDataDefinition('string');
    $definition->addConstraint($constraint);
    $value = StringData::createInstance($definition);
    $value->setValue($input);

    return $value->validate()->count() == 0;
  }

}
