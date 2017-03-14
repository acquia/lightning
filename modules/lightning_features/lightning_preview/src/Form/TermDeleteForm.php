<?php

namespace Drupal\lightning_preview\Form;

use Drupal\taxonomy\Form\TermDeleteForm as BaseTermDeleteForm;

/**
 * A Multiversion-aware version of TermDeleteForm.
 */
class TermDeleteForm extends BaseTermDeleteForm {

  use EntityDeleteFormPurgeTrait;

}
