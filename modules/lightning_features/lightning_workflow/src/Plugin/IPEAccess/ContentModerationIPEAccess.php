<?php

namespace Drupal\lightning_workflow\Plugin\IPEAccess;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant;
use Drupal\panels_ipe\Plugin\IPEAccessBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @IPEAccess(
 *   id = "content_moderation_ipe",
 *   label = @Translation("Content moderation")
 * )
 */
class ContentModerationIPEAccess extends IPEAccessBase implements ContainerFactoryPluginInterface {

  /**
   * The moderation information service.
   *
   * @var \Drupal\content_moderation\ModerationInformationInterface
   */
  protected $information;

  /**
   * ContentModerationIPEAccess constructor.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \Drupal\content_moderation\ModerationInformationInterface $information
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModerationInformationInterface $information) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->information = $information;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('content_moderation.moderation_information')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applies(PanelsDisplayVariant $display) {
    $contexts = $display->getContexts();

    if (empty($contexts['@panelizer.entity_context:entity'])) {
      return FALSE;
    }

    $context = $contexts['@panelizer.entity_context:entity'];
    if ($context->hasContextValue()) {
      return $this->information->isModeratedEntity($context->getContextValue());
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function access(PanelsDisplayVariant $display) {
    $entity = $display->getContexts()['@panelizer.entity_context:entity']->getContextValue();
    return $this->information->isLatestRevision($entity) && !$this->information->isLiveRevision($entity);
  }

}
