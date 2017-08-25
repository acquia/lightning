<?php

namespace Drupal\lightning_workflow\Plugin\Update;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\lightning\UpdateBase;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Executes interactive update steps for Lightning Workflow 2.0.3.
 *
 * @Update("2.0.3")
 */
class Update203 extends UpdateBase {

  /**
   * The view entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $viewStorage;

  /**
   * Update203 constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Symfony\Component\Console\Style\OutputStyle $io
   *   The console output driver.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   The string translation service.
   * @param \Drupal\Core\Entity\EntityStorageInterface $view_storage
   *   The view entity storage handler.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, OutputStyle $io, TranslationInterface $translation, EntityStorageInterface $view_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $io, $translation);
    $this->viewStorage = $view_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, OutputStyle $io = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $io,
      $container->get('string_translation'),
      $container->get('entity_type.manager')->getStorage('view')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $views = $this->viewStorage->getQuery()->execute();

    if (in_array('content', $views)) {
      $this->confirm('addForwardRevisionIndicator');
    }
  }

  /**
   * @ask Do you want to add a column to the administrative content view to
   * indicate the presence of forward revisions?
   */
  protected function addForwardRevisionIndicator() {
    /** @var \Drupal\views\ViewEntityInterface $view */
    $view = $this->viewStorage->load('content');

    $display_options = &$view->getDisplay('default')['display_options'];
    $display_options['relationships']['latest_revision__node'] = unserialize('a:8:{s:2:"id";s:21:"latest_revision__node";s:5:"table";s:26:"workbench_revision_tracker";s:5:"field";s:21:"latest_revision__node";s:12:"relationship";s:4:"none";s:10:"group_type";s:5:"group";s:11:"admin_label";s:15:"latest revision";s:8:"required";b:1;s:9:"plugin_id";s:8:"standard";}');
    $display_options['fields']['forward_revision_exists'] = unserialize('a:23:{s:2:"id";s:23:"forward_revision_exists";s:5:"table";s:15:"node_field_data";s:5:"field";s:23:"forward_revision_exists";s:12:"relationship";s:4:"none";s:10:"group_type";s:5:"group";s:11:"admin_label";s:0:"";s:5:"label";s:21:"Has unpublished edits";s:7:"exclude";b:0;s:5:"alter";a:26:{s:10:"alter_text";b:0;s:4:"text";s:0:"";s:9:"make_link";b:0;s:4:"path";s:0:"";s:8:"absolute";b:0;s:8:"external";b:0;s:14:"replace_spaces";b:0;s:9:"path_case";s:4:"none";s:15:"trim_whitespace";b:0;s:3:"alt";s:0:"";s:3:"rel";s:0:"";s:10:"link_class";s:0:"";s:6:"prefix";s:0:"";s:6:"suffix";s:0:"";s:6:"target";s:0:"";s:5:"nl2br";b:0;s:10:"max_length";i:0;s:13:"word_boundary";b:1;s:8:"ellipsis";b:1;s:9:"more_link";b:0;s:14:"more_link_text";s:0:"";s:14:"more_link_path";s:0:"";s:10:"strip_tags";b:0;s:4:"trim";b:0;s:13:"preserve_tags";s:0:"";s:4:"html";b:0;}s:12:"element_type";s:0:"";s:13:"element_class";s:0:"";s:18:"element_label_type";s:0:"";s:19:"element_label_class";s:0:"";s:19:"element_label_colon";b:1;s:20:"element_wrapper_type";s:0:"";s:21:"element_wrapper_class";s:0:"";s:23:"element_default_classes";b:1;s:5:"empty";s:0:"";s:10:"hide_empty";b:0;s:10:"empty_zero";b:0;s:16:"hide_alter_empty";b:1;s:11:"entity_type";s:4:"node";s:9:"plugin_id";s:23:"forward_revision_exists";}');
    $this->viewStorage->save($view);
  }

}
