<?php

namespace Acquia\LightningExtension\Context;

use Behat\Gherkin\Node\PyStringNode;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Entity\EntityViewModeInterface;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Webmozart\Assert\Assert;

/**
 * A context for working with entity display modes.
 */
class DisplayModeContext extends DrupalSubContextBase {

  /**
   * Sets the customization status of a view mode.
   *
   * @param string $entity_type
   *   The ID of the affected entity type.
   * @param string $bundle
   *   (optional) The ID of the affected bundle. If omitted, the entity type is
   *   presumed to not support bundles.
   * @param string $view_mode
   *   (optional) The view mode ID. Defaults to 'default'.
   * @param bool $desired_status
   *   (optional) Whether to customize or uncustomize the view mode.
   *
   * @When I customize :entity_type display
   * @When I customize the :view_mode :entity_type display
   * @When I customize the display of the :bundle :entity_type type
   * @When I customize the :view_mode display of the :bundle :entity_type type
   */
  public function setViewModeCustomization($entity_type, $bundle = NULL, $view_mode = 'default', $desired_status = TRUE) {
    $display = entity_get_display($entity_type, $bundle, $view_mode);

    if ($display->isNew()) {
      $display = entity_get_display($entity_type, $bundle, 'default')
        ->createDuplicate()
        ->set('mode', $view_mode);

      // If the display was created anew, its previous status is effectively
      // FALSE.
      $original_status = FALSE;
    }
    else {
      $original_status = $display->status();
    }

    /** @var UndoContext $undo */
    $undo = $this->getContext(UndoContext::class);
    if ($undo && $desired_status != $original_status) {
      $arguments = array_slice(func_get_args(), 0, 3);
      $arguments[] = $original_status;
      $undo->push([$this, __FUNCTION__], $arguments);
    }

    $display->setStatus($desired_status)->save();
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
    $view_mode = EntityViewMode::load($id);

    Assert::isInstanceOf($view_mode, EntityViewModeInterface::class);

    /** @var UndoContext $undo */
    $undo = $this->getContext(UndoContext::class);
    if ($undo) {
      $original_description = $view_mode
        ->getThirdPartySetting('lightning_core', 'description');

      $original = new PyStringNode([$original_description], 0);
      $undo->push([$this, __FUNCTION__], [$id, $original]);
    }

    $view_mode
      ->setThirdPartySetting('lightning_core', 'description', (string) $description)
      ->save();
  }

}
