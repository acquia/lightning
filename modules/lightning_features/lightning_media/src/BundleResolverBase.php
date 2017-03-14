<?php

namespace Drupal\lightning_media;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\media_entity\MediaBundleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for media bundle resolvers.
 *
 * @deprecated in Lightning 2.0.4 and will be removed in Lightning 2.1.0. Media
 * type plugins should implement InputMatchInterface directly instead.
 */
class BundleResolverBase extends PluginBase implements BundleResolverInterface, ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The currently logged in user.
   *
   * @var AccountInterface
   */
  protected $currentUser;

  /**
   * BundleResolverBase constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param AccountInterface $current_user
   *   The currently logged in user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('current_user')
    );
  }

  /**
   * Returns all available media bundles.
   *
   * @return MediaBundleInterface[]
   *   All available media bundles for which the current user has create access.
   */
  public function getPossibleBundles() {
    $access_handler = $this->entityTypeManager->getAccessControlHandler('media');

    return array_filter(
      $this->entityTypeManager
        ->getStorage('media_bundle')
        ->loadMultiple(),

      function (MediaBundleInterface $bundle) use ($access_handler) {
        return $access_handler->createAccess($bundle->id(), $this->currentUser);
      }
    );
  }

  /**
   * Returns the first available media bundle that can handle an input value.
   *
   * @param mixed $input
   *   The input value.
   *
   * @return MediaBundleInterface|false
   *   A media bundle which can handle the input, or FALSE if there are none.
   */
  public function getBundle($input) {
    foreach ($this->getPossibleBundles() as $bundle) {
      $plugin = $bundle->getType();
      if ($plugin instanceof InputMatchInterface && $plugin->appliesTo($input, $bundle)) {
        return $bundle;
      }
    }
    return FALSE;
  }

}
