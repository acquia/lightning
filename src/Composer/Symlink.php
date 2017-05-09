<?php

namespace Acquia\Lightning\Composer;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Links the git hook pre-commit file to the one provided in this repo.
 */
class Symlink {

  /**
   * Script entry point.
   */
  public static function execute(Event $event) {
    $fs = new Filesystem();
    if ($fs->exists('./.git/hooks/pre-commit')) {
      // Delete the pre-commit directory if it exists.
      $fs->remove('./.git/hooks/pre-commit');
    }
    $fs->symlink(__DIR__ . '/../../git-hooks/pre-commit', './.git/hooks/pre-commit');
    $fs->touch('./.git/hooks/foo');
    $event->getIO()->write('Symlinked git hooks.');
  }

}
