<?php

namespace Drupal\lightning_inline_block\Form;

use Drupal\block_content\BlockContentForm;
use Drupal\Core\Entity\RevisionableInterface;
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

  protected function toInline() {
    $entity = $this->getEntity();

    $values = array_filter($entity->toArray());

    foreach ($entity->getEntityType()->getKeys() as $key) {
      if (isset($values[$key])) {
        $values[$key] = reset($values[$key][0]);
      }
    }

    $entity = $this->entityTypeManager
      ->getStorage('inline_block_content')
      ->create($values);

    $this->setEntity($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    if ($form_state->getValue('global')) {
      $this->toInline();
    }
    parent::save($form, $form_state);

    $entity = $this->getEntity();

    $configuration = [
      'label' => $entity->label(),
      'region' => $form_state->getValue('region'),
    ];
    if ($form_state->getValue('global')) {
      $configuration['id'] = 'block_content:' . $entity->uuid();
    }
    else {
      $configuration['id'] = 'inline_entity';
      $configuration['entity'] = serialize($entity);
    }

    /** @var \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant $display */
    $display = $form_state->get('panels_display');

    $display->addBlock($configuration);
    $this->tempStore->set($display->getTempStoreId(), $display->getConfiguration());

    $contexts = $display->getContexts();
    $entity = $contexts['@panelizer.entity_context:entity']->getContextValue();

    if ($entity instanceof RevisionableInterface && !$entity->isDefaultRevision() && $entity->getEntityType()->hasLinkTemplate('latest-version')) {
      $rel = 'latest-version';
    }
    else {
      $rel = 'canonical';
    }
    $form_state->setRedirectUrl($entity->toUrl($rel));
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant $display */
    $display = $form_state->get('panels_display');

    $form['region'] = [
      '#type' => 'select',
      '#title' => $this->t('Region'),
      '#required' => TRUE,
      '#options' => $display->getRegionNames(),
      '#default_value' => $display->getLayout()->getPluginDefinition()->getDefaultRegion(),
    ];
    $form['global'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Make this content reusable'),
    ];
    return $form;
  }

}
