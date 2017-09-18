<?php

namespace Drupal\lightning_workflow\Update;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\lightning_core\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Executes interactive update steps for Lightning Workflow 2.1.8.
 *
 * @Update("2.1.8")
 */
final class Update218 implements ContainerInjectionInterface {

  /**
   * The view entity storage handler.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $viewStorage;

  public function __construct(EntityStorageInterface $view_storage = NULL) {
    $this->viewStorage = $view_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $arguments = [];

    if ($container->get('module_handler')->moduleExists('views')) {
      $arguments[] = $container->get('entity_type.manager')->getStorage('view');
    }

    return (new \ReflectionClass(__CLASS__))->newInstanceArgs($arguments);
  }

  /**
   * @update
   *
   * @ask Do you want to make the Operations drop-button the last column in each
   * row of the Content view?
   */
  public function moveContentViewOperations() {
    if (empty($this->viewStorage)) {
      return;
    }

    /** @var \Drupal\views\ViewEntityInterface $view */
    $view = $this->viewStorage->load('content');
    if (empty($view)) {
      return;
    }

    $display = &$view->getDisplay('default');

    if (isset($display['display_options']['fields']['operations'])) {
      Element::toTail($display['display_options']['fields'], 'operations');
      $this->viewStorage->save($view);
    }
  }

}
