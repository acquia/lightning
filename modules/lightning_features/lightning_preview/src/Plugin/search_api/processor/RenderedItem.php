<?php

namespace Drupal\lightning_preview\Plugin\search_api\processor;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Theme\ThemeInitializationInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\lightning_preview\ExplicitWorkspaceNegotiator;
use Drupal\multiversion\Workspace\WorkspaceManagerInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Plugin\search_api\processor\RenderedItem as BaseRenderedItem;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A workspace-aware implementation of Search API's rendered_item processor.
 */
class RenderedItem extends BaseRenderedItem {

  /**
   * The workspace manager.
   *
   * @var WorkspaceManagerInterface
   */
  protected $workspaceManager;

  /**
   * The explicit workspace negotiator.
   *
   * @var ExplicitWorkspaceNegotiator
   */
  protected $explicitNegotiator;

  /**
   * RenderedItem constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param array $plugin_definition
   *   The plugin definition.
   * @param AccountProxyInterface $current_user
   *   The currently logged in user.
   * @param RendererInterface $renderer
   *   The renderer.
   * @param LoggerChannelInterface $logger_channel
   *   The Search API logger channel.
   * @param ThemeManagerInterface $theme_manager
   *   The theme manager.
   * @param ThemeInitializationInterface $theme_initializer
   *   The theme initializer.
   * @param ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param WorkspaceManagerInterface $workspace_manager
   *   The workspace manager.
   * @param ExplicitWorkspaceNegotiator $explicit_negotiator
   *   The explicit workspace negotiator.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, AccountProxyInterface $current_user, RendererInterface $renderer, LoggerChannelInterface $logger_channel, ThemeManagerInterface $theme_manager, ThemeInitializationInterface $theme_initializer, ConfigFactoryInterface $config_factory, WorkspaceManagerInterface $workspace_manager, ExplicitWorkspaceNegotiator $explicit_negotiator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->setCurrentUser($current_user);
    $this->setRenderer($renderer);
    $this->setLogger($logger_channel);
    $this->setThemeManager($theme_manager);
    $this->setThemeInitializer($theme_initializer);
    $this->setConfigFactory($config_factory);
    $this->workspaceManager = $workspace_manager;
    $this->explicitNegotiator = $explicit_negotiator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_user'),
      $container->get('renderer'),
      $container->get('logger.channel.search_api'),
      $container->get('theme.manager'),
      $container->get('theme.initialization'),
      $container->get('config.factory'),
      $container->get('workspace.manager'),
      $container->get('workspace.negotiator.explicit')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item) {
    $workspace_id = $this->workspaceManager->getActiveWorkspace()->id();
    $this->explicitNegotiator->setWorkspace($workspace_id);
    parent::addFieldValues($item);
    $this->explicitNegotiator->setWorkspace();
  }

}
