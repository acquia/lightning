<?php

namespace Drupal\lightning_inline_block\Form;

use Drupal\block_content\BlockContentForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\SharedTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InlineContentForm extends BlockContentForm {

  /**
   * The Panels IPE temp store.
   *
   * @var \Drupal\user\SharedTempStore
   */
  protected $tempStore;

  /**
   * InlineContentForm constructor.
   *
   * @param \Drupal\user\SharedTempStore $temp_store
   *   The Panels IPE temp store.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(SharedTempStore $temp_store, $entity_manager, $entity_type_bundle_info = NULL, $time = NULL) {
    $this->tempStore = $temp_store;
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('user.shared_tempstore')->get('panels_ipe'),
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    /** @var \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant $display */
    $display = $form_state->get('display');

    $display->addBlock([
      'id' => 'inline_entity',
      'region' => $display->getLayout()->getPluginDefinition()->getDefaultRegion(),
      'entity' => serialize($this->getEntity()),
    ]);
    $this->tempStore->set($display->getTempStoreId(), $display->getConfiguration());

    $contexts = $display->getContexts();
    $form_state->setRedirectUrl(
      $contexts['@panelizer.entity_context:entity']->getContextValue()->toUrl()
    );
  }

}
