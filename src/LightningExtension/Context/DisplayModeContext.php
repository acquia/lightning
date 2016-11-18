<?php

namespace Acquia\LightningExtension\Context;

use Behat\Gherkin\Node\PyStringNode;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Url;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * A context for working with entity display modes.
 */
class DisplayModeContext extends DrupalSubContextBase {

  /**
   * The Mink context.
   *
   * @var MinkContext
   */
  protected $minkContext;

  /**
   * Gathers required contexts.
   *
   * @BeforeScenario
   */
  public function gatherContexts() {
    $this->minkContext = $this->getContext(MinkContext::class);
  }

  /**
   * Determines the ID of a view mode.
   *
   * @param string $entity_type
   *   The entity type ID.
   * @param string $view_mode
   *   The view mode ID or label.
   *
   * @return string
   *   The view mode ID, without the entity type ID (i.e., node.rss --> rss).
   */
  protected function getViewModeId($entity_type, $view_mode) {
    $entity = EntityViewMode::load($entity_type . '.' . $view_mode);

    if (empty($entity)) {
      $loaded = \Drupal::entityTypeManager()
        ->getStorage('entity_view_mode')
        ->loadByProperties([
          'targetEntityType' => $entity_type,
          'label' => $view_mode,
        ]);

      $entity = reset($loaded);
    }

    return explode('.', $entity->id())[1];
  }

  /**
   * Visits the configuration page for an entity view display.
   *
   * @param string $entity_type
   *   The entity type ID.
   * @param string $view_mode
   *   The view mode ID or label. If 'default', will visit the special default
   *   view display.
   * @param string $bundle
   *   (optional) The bundle ID. If omitted, the entity type will be presumed
   *   to not support bundles.
   *
   * @When I configure the :view_mode display of the :bundle :entity_type type
   * @When I configure the :view_mode display of :entity_type(s)
   */
  public function configureViewDisplay($entity_type, $view_mode, $bundle = NULL) {
    $route = 'entity.entity_view_display.' . $entity_type;

    if ($view_mode == 'default') {
      $route .= '.default';
      $parameters = [];
    }
    else {
      $route .= '.view_mode';
      $parameters = [
        'view_mode_name' => $this->getViewModeId($entity_type, $view_mode),
      ];
    }

    if ($bundle) {
      $key = \Drupal::entityTypeManager()
        ->getDefinition($entity_type)
        ->getBundleEntityType();

      $parameters[$key] = $bundle;
    }

    $this->visitPath(Url::fromRoute($route, $parameters)->getInternalPath());
  }

  /**
   * Sets the customization status of a view mode.
   *
   * @param string $view_mode
   *   The label of the view mode.
   * @param string $entity_type
   *   The ID of the affected entity type.
   * @param string $bundle
   *   (optional) The ID of the affected bundle. If omitted, the entity type is
   *   presumed to not support bundles.
   * @param bool $customize
   *   (optional) Whether to customize or uncustomize the view mode.
   *
   * @When I customize the :view_mode display of the :bundle :entity_type type
   * @When I customize the :view_mode :entity_type display
   */
  public function setViewModeCustomization($view_mode, $entity_type, $bundle = NULL, $customize = TRUE) {
    $this->configureViewDisplay($entity_type, 'default', $bundle);

    $customize
      ? $this->minkContext->checkOption($view_mode)
      : $this->minkContext->uncheckOption($view_mode);

    /** @var UndoContext $undo */
    $undo = $this->getContext(UndoContext::class);
    if ($undo) {
      $arguments = array_slice(func_get_args(), 0, 3);
      $arguments[] = !$customize;
      $undo->push([$this, __FUNCTION__], $arguments);
    }

    $this->minkContext->pressButton('Save');
  }

  /**
   * Sets a description on an entity view mode.
   *
   * @param string $id
   *   The view mode ID.
   * @param \Behat\Gherkin\Node\PyStringNode $description
   *   The view mode description.
   *
   * @When I describe the :id view mode:
   */
  public function describeViewMode($id, PyStringNode $description) {
    $this->visitPath('/admin/structure/display-modes/view/manage/' . $id);

    /** @var UndoContext $undo */
    $undo = $this->getContext(UndoContext::class);
    if ($undo) {
      $original = new PyStringNode(
        (array) $this->assertSession()->fieldExists('Description')->getValue(),
        0
      );
      $undo->push([$this, __FUNCTION__], [$id, $original]);
    }

    $this->minkContext->fillField('Description', (string) $description);
    $this->minkContext->pressButton('Save');
  }

}
