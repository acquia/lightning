<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\Component\Utility\Random;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Context\Step;
use Behat\Behat\Context\Step\Given;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {
  // LightingSubContext is automatically loaded by the Drupal Behat Extension.
  // @see lightning.behat.inc.
  // @see http://behat-drupal-extension.readthedocs.org/en/3.1/subcontexts.html#for-contributors
}
