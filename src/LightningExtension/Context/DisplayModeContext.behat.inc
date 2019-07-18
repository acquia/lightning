<?php

namespace Acquia\LightningExtension\Context;

@trigger_error(__NAMESPACE__ . '\DisplayModeContext is deprecated in lightning:8.x-4.0 and will be removed in lightning:8.x-4.1. If you need its functionality, you should copy the relevant code into your own project. See https://www.drupal.org/node/3068751', E_USER_DEPRECATED);

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
    @trigger_error(__METHOD__ . ' is deprecated in lightning:8.x-4.0 and will be removed in lightning:8.x-4.1. If you need its functionality, you should copy the relevant code into your own project. See https://www.drupal.org/node/3068751', E_USER_DEPRECATED);

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
