<?php

namespace Acquia\LightningExtension\Context;

use Behat\Gherkin\Node\PyStringNode;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Entity\EntityViewModeInterface;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Webmozart\Assert\Assert;

/**
 * A context for working with entity display modes.
 *
 * @internal
 *   This is an internal part of Lightning's testing system and may be changed
 *   or removed at any time without warning. External code should not extend,
 *   instantiate, or rely on this class in any way! If you'd like to use any of
 *   these step definitions in your project, you should copy them into your own
 *   project.
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
