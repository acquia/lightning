<?php

/**
 * @file
 * Contains \EntitySubContext.
 */

use Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;
use Drupal\DrupalExtension\Context\DrupalSubContextInterface;

/**
 * Subcontext for creating and cleaning up entities of any type.
 */
class EntitySubContext extends DrupalSubContextBase implements DrupalSubContextInterface {

  /**
   * Entities created during the scenario.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected $entities = [];

  /**
   * Creates a set of entities.
   *
   * @param \Behat\Gherkin\Node\TableNode $table
   *   The entities to create.
   *
   * @Given :entity_type entities:
   */
  public function createEntities($entity_type, TableNode $table) {
    if ($this->getDriver()->isBootstrapped()) {
      foreach ($table as $row) {
        $this->entities[] = \Drupal::entityTypeManager()
          ->getStorage($entity_type)
          ->create($row);

        end($this->entities)->save();
      }
    }
    else {
      throw new \RuntimeException('Drupal is not bootstrapped.');
    }
  }

  /**
   * Deletes all entities created during the scenario.
   *
   * @AfterScenario
   */
  public function cleanEntities() {
    foreach ($this->entities as $entity) {
      $entity->delete();
    }
  }

}
