<?php

namespace Drupal\lightning_layout\Form;

use Drupal\block_content\BlockContentForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\SharedTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;

class InlineBlockContentForm extends BlockContentForm {

  /**
   * The Panels IPE temp store.
   *
   * @var \Drupal\user\SharedTempStore
   */
  protected $tempStore;

  /**
   * InlineBlockContentForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\user\SharedTempStore $temp_store
   *   The Panels IPE temp store.
   */
  public function __construct($entity_manager, $entity_type_bundle_info = NULL, $time = NULL, SharedTempStore $temp_store) {
    parent::__construct($entity_manager, $entity_type_bundle_info, $time);
    $this->tempStore = $temp_store;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('user.shared_tempstore')->get('panels_ipe')
    );
  }

  /**
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant
   * @throws \Exception
   */
  protected function ensureDisplay(FormStateInterface $form_state) {
    /** @var \Drupal\panels\Plugin\DisplayVariant\PanelsDisplayVariant $display */
    $display = $form_state->get('panels_display');

    if ($display) {
      $configuration = $this->tempStore->get($display->getTempStoreId());

      return $configuration
        ? $display->setConfiguration($configuration)
        : $display;
    }
    else {
      throw new \Exception('The Panels display variant is unavailable.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    $display = $this->ensureDisplay($form_state);
    $display->addBlock([
      'id' => 'inline_entity',
      'region' => $form_state->getValue('_region'),
      'entity' => serialize($this->getEntity()),
    ]);

    $this->tempStore->set($display->getTempStoreId(), $display->getConfiguration());

    if ($form_state->has('referrer')) {
      $redirect = $form_state->get('referrer');
      $redirect = Url::fromUri($redirect);

      $form_state->setRedirectUrl($redirect);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $regions = $this->ensureDisplay($form_state)->getRegionNames();

    $form['_region'] = [
      '#type' => 'select',
      '#title' => $this->t('Region'),
      '#required' => TRUE,
      '#options' => $regions,
      '#default_value' => key($regions),
    ];

    return $form;
  }

}
