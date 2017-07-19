<?php

namespace Drupal\lightning_inline_block\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Form\FormStateInterface;
use Drupal\lightning_inline_block\Ajax\RefreshCommand;
use Drupal\panels_ipe\Form\PanelsIPEBlockContentForm;
use Drupal\user\SharedTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InlineContentForm extends PanelsIPEBlockContentForm {

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
  public function buildEntity(array $form, FormStateInterface $form_state) {
    $entity = parent::buildEntity($form, $form_state);

    if ($form_state->getValue('global')) {
      return $entity;
    }
    else {
      $values = array_filter($entity->toArray());

      foreach ($entity->getEntityType()->getKeys() as $key) {
        if (isset($values[$key])) {
          $values[$key] = reset($values[$key][0]);
        }
      }
      return $this->entityTypeManager
        ->getStorage('inline_block_content')
        ->create($values);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->getEntity();

    if ($form_state->getValue('global')) {
      parent::save($form, $form_state);

      $configuration = [
        'id' => 'block_content:' . $entity->uuid(),
        'label' => $entity->label(),
      ];
    }
    else {
      $entity->save();

      $configuration = [
        'id' => 'inline_entity',
        'label' => $form_state->getValue(['info', 0, 'value']),
        'entity' => serialize($entity),
      ];
    }
    $configuration['region'] = $form_state->getValue('region');

    /** @var \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant $display */
    $display = $form_state->get('panels_display');

    $display->addBlock($configuration);
    $this->tempStore->set($display->getTempStoreId(), $display->getConfiguration());
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

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $return = parent::submitForm($form, $form_state);

    return $form_state->hasAnyErrors()
      ? $return
      : (new AjaxResponse)->addCommand(new RefreshCommand);
  }

}
