<?php

namespace Acquia\LightningExtension\Context;

use Drupal\DrupalExtension\Context\DrupalSubContextBase;

@trigger_error(__NAMESPACE__ . '\UndoContext is deprecated in lightning:8.x-4.0 and will be removed in lightning:8.x-4.1. If you need its functionality, you should copy the relevant code into your own project. See https://www.drupal.org/node/3068751', E_USER_DEPRECATED);

/**
 * A context allowing easy undo of anything done during a scenario.
 */
class UndoContext extends DrupalSubContextBase {

  /**
   * Whether the stack is locked (i.e., not accepting new operations).
   *
   * @var bool
   */
  protected $locked = FALSE;

  /**
   * The operation stack.
   *
   * @var array
   */
  protected $stack = [];

  /**
   * Executes all undo operations in the stack.
   *
   * @AfterScenario
   */
  public function runAll() {
    @trigger_error(__METHOD__ . ' is deprecated in lightning:8.x-4.0 and will be removed in lightning:8.x-4.1. If you need its functionality, you should copy the relevant code into your own project. See https://www.drupal.org/node/3068751', E_USER_DEPRECATED);

    $this->locked = TRUE;

    while ($this->stack) {
      $operation = array_pop($this->stack);
      call_user_func_array($operation[0], $operation[1]);
    }

    $this->locked = FALSE;
  }

  /**
   * Adds an operation to the undo stack.
   *
   * @param callable $callback
   *   The function to call.
   * @param array $arguments
   *   (optional) Arguments to pass to the callback. None of the arguments can
   *   be passed by reference.
   */
  public function push(callable $callback, array $arguments = []) {
    @trigger_error(__METHOD__ . ' is deprecated in lightning:8.x-4.0 and will be removed in lightning:8.x-4.1. If you need its functionality, you should copy the relevant code into your own project. See https://www.drupal.org/node/3068751', E_USER_DEPRECATED);

    if ($this->locked == FALSE) {
      $this->stack[] = func_get_args();
    }
  }

}
