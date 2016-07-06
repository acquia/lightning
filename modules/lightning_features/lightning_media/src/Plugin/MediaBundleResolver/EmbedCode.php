<?php

namespace Drupal\lightning_media\Plugin\MediaBundleResolver;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\TypedData\Plugin\DataType\StringData;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\lightning_media\BundleResolverBase;
use Drupal\video_embed_field\ProviderManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Bundle resolver for embed codes.
 *
 * @MediaBundleResolver(
 *   id = "embed_code",
 *   field_types = {"string", "string_long", "video_embed_field"}
 * )
 */
class EmbedCode extends BundleResolverBase {

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedData;

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
   * @param \Drupal\video_embed_field\ProviderManagerInterface $video_providers
   *   (optional) The video provider manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, TypedDataManagerInterface $typed_data_manager, ProviderManagerInterface $video_providers = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager);
    $this->typedData = $typed_data_manager;
    $this->videoProviders = $video_providers;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $arguments = array(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('typed_data_manager'),
    );
    try {
      $arguments[] = $container->get('video_embed_field.provider_manager');
    }
    catch (ServiceNotFoundException $e) {
      // This service is optional, don't worry if we don't have it.
    }
    return (new \ReflectionClass(__CLASS__))->newInstanceArgs($arguments);
  }

  /**
   * {@inheritdoc}
   */
  public function getBundle($input) {
    switch (TRUE) {
      case $this->isVideo($input):
        return $this->bundleStorage->load('video');

      case $this->isTweet($input):
        return $this->bundleStorage->load('tweet');

      case $this->isInstagram($input):
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
    try {
      return $this->validateAs('TweetEmbedCode', $embed_code);
    }
    catch (PluginNotFoundException $e) {
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
    try {
      return $this->validateAs('InstagramEmbedCode', $embed_code);
    }
    catch (PluginNotFoundException $e) {
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
  protected function validateAs($constraint, $input) {
    $definition = $this->typedData->createDataDefinition('string');
    $definition->addConstraint($constraint);
    $value = StringData::createInstance($definition);
    $value->setValue($input);

    return $value->validate()->count() == 0;
  }

}
