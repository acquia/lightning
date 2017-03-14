<?php

namespace Drupal\lightning_layout\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The settings form for controlling Lightning Layout's behavior.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The block plugin manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface|\Drupal\Core\Block\BlockManager
   */
  protected $blockManager;

  /**
   * The entity block deriver.
   *
   * @var \Drupal\entity_block\Plugin\Derivative\EntityBlock
   */
  protected $deriver;

  /**
   * SettingsForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block plugin manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The string translation service.
   * @param mixed $deriver
   *   (optional) The entity block deriver. If passed, must be an instance of
   *   \Drupal\entity_block\Plugin\Derivative\EntityBlock.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, BlockManagerInterface $block_manager, TranslationInterface $translator, $deriver = NULL) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->blockManager = $block_manager;
    $this->setStringTranslation($translator);
    $this->deriver = $deriver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $arguments = [
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.block'),
      $container->get('string_translation'),
    ];

    // Entity Block is not a hard dependency of Lightning Layout, so we need
    // to be careful not to inject its deriver if it's not available.
    $deriver = 'Drupal\entity_block\Plugin\Derivative\EntityBlock';
    if (class_exists($deriver)) {
      $arguments[] = call_user_func([$deriver, 'create'], $container, 'entity_block');
    }

    $reflector = new \ReflectionClass(static::class);
    return $reflector->newInstanceArgs($arguments);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['lightning_layout.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lightning_layout_settings_form';
  }

  /**
   * Allows access if the Entity Block deriver is available.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   Whether access is allowed.
   */
  public function access() {
    return AccessResult::allowedIf(
      (bool) $this->deriver
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['entity_blocks'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Entity types to expose as blocks'),
      '#default_value' => $this->config('lightning_layout.settings')->get('entity_blocks'),
    ];

    // Get the definitions of all entity types supported by Entity Block.
    /** @var \Drupal\Core\Entity\EntityTypeInterface[] $available_types */
    $available_types = array_intersect_key(
      $this->entityTypeManager->getDefinitions(),
      $this->deriver->getDerivativeDefinitions([])
    );
    foreach ($available_types as $id => $entity_type) {
      $form['entity_blocks']['#options'][$id] = $entity_type->getLabel();
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('entity_blocks');
    // Filter out unselected entity types.
    $value = array_filter($value);
    // Re-key the array.
    $value = array_values($value);

    $this->config('lightning_layout.settings')
      ->set('entity_blocks', $value)
      ->save();

    $this->blockManager->clearCachedDefinitions();

    parent::submitForm($form, $form_state);
  }

}
