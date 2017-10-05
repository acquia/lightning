<?php

namespace Drupal\lightning_workflow\Update;

use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\node\NodeTypeInterface;
use Drupal\workbench_moderation\ModerationInformationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Executes interactive update steps for Lightning Workflow 2.2.1.
 *
 * @Update("2.2.1")
 */
final class Update221 implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The node type entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeTypeStorage;

  /**
   * The node entity type definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $nodeDefinition;

  /**
   * The moderation information service.
   *
   * @var \Drupal\workbench_moderation\ModerationInformationInterface
   */
  protected $moderationInfo;

  public function __construct(EntityStorageInterface $node_type_storage, EntityTypeInterface $node_definition, ModerationInformationInterface $moderation_info, TranslationInterface $translation) {
    $this->nodeTypeStorage = $node_type_storage;
    $this->nodeDefinition = $node_definition;
    $this->moderationInfo = $moderation_info;
    $this->setStringTranslation($translation);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('node_type'),
      $container->get('entity_type.manager')->getDefinition('node'),
      $container->get('workbench_moderation.moderation_information'),
      $container->get('string_translation')
    );
  }

  /**
   * Returns all moderated node types.
   *
   * @return NodeTypeInterface[]
   */
  protected function getNodeTypes() {
    $filter = function (NodeTypeInterface $node_type) {
      return $this->moderationInfo->isModeratableBundle($this->nodeDefinition, $node_type->id());
    };
    return array_filter($this->nodeTypeStorage->loadMultiple(), $filter);
  }

  /**
   * @update
   */
  public function hideStatusCheckboxes(DrupalStyle $io) {
    /** @var NodeTypeInterface $node_type */
    foreach ($this->getNodeTypes() as $node_type) {
      $question = (string) $this->t('Do you want to ensure the "Publishing status" checkbox is hidden on the @node_type content type form?', [
        '@node_type' => $node_type->label(),
      ]);

      if ($io->confirm($question)) {
        entity_get_form_display('node', $node_type->id(), 'default')
          ->removeComponent('status')
          ->save();
      }
    }
  }

}
