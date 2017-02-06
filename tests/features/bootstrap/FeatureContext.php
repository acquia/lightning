<?php

/**
 * @file
 * Contains \FeatureContext.
 */

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;

/**
 * Defines application features from the specific context.
 *
 * LightingSubContext is automatically loaded by the Drupal Behat Extension.
 *
 * @see lightning.behat.inc.
 * @see http://behat-drupal-extension.readthedocs.org/en/3.1/subcontexts.html#for-contributors
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {
}
