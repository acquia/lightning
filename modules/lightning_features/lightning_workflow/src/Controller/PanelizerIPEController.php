<?php

namespace Drupal\lightning_workflow\Controller;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\panelizer\Controller\PanelizerPanelsIPEController;
use Drupal\panelizer\PanelizerInterface;
use Drupal\workbench_moderation\ModerationInformationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for Panels IPE routes that are specific to Panelizer.
 */
class PanelizerIPEController extends PanelizerPanelsIPEController {

  /**
   * The moderation information service.
   *
   * @var \Drupal\workbench_moderation\ModerationInformationInterface
   */
  protected $modInfo;

  /**
   * PanelizerIPEController constructor.
   *
   * @param PanelizerInterface $panelizer
   *   The Panelizer service.
   * @param ModerationInformationInterface $mod_info
   *   The moderation information service.
   */
  public function __construct(PanelizerInterface $panelizer, ModerationInformationInterface $mod_info) {
    parent::__construct($panelizer);
    $this->modInfo = $mod_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('panelizer'),
      $container->get('workbench_moderation.moderation_information')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function revertToDefault(FieldableEntityInterface $entity, $view_mode) {
    if ($this->modInfo->isModeratableEntity($entity)) {
      $entity = $this->modInfo->getLatestRevision($entity->getEntityTypeId(), $entity->id());
    }
    return parent::revertToDefault($entity, $view_mode);
  }

}
