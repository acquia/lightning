<?php

namespace Drupal\lightning_workflow\Event;

final class QuickEditEvents {

  /**
   * Event fired when Quick Edit access is being determined.
   *
   * @var string
   *
   * @see lightning_workflow_entity_view_alter()
   * @see lightning_workflow_preprocess_field()
   *
   * @Event
   */
  const ACCESS = 'quickedit.access';

}
