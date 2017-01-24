<?php

namespace Acquia\LightningExtension\Context;

use Drupal\DrupalExtension\Context\DrupalSubContextBase;

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
    if ($this->locked == FALSE) {
      $this->stack[] = func_get_args();
    }
  }

}
