<?php

namespace Drupal\lightning_preview\Form;

use Drupal\node\Form\NodeDeleteForm as BaseNodeDeleteForm;

/**
 * A Multiversion-aware version of NodeDeleteForm.
 */
class NodeDeleteForm extends BaseNodeDeleteForm {

  use EntityDeleteFormPurgeTrait;

}
