<?php

namespace Drupal\lightning_core\Form;

use Drupal\field_ui\Form\EntityDisplayModeEditForm as BaseEntityDisplayModeEditForm;
use Drupal\lightning_core\EntityDescriptionFormTrait;

/**
 * Adds description support to the entity edit form for entity display modes.
 */
class EntityDisplayModeEditForm extends BaseEntityDisplayModeEditForm {

  use EntityDescriptionFormTrait;

}
