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
      // Delete the pre-commit file/symlink if it exists.
      // @todo once we can update to symfony/filesystem:3.2 we can be a little
      // smarter here and see if it already links to the correct place and bail
      // out if so - so that we're not constantly rewriting this link. 3.2
      // currently conflicts with DrupalConsole.
      $fs->remove('./.git/hooks/pre-commit');
    }
    $fs->symlink(__DIR__ . '/../../git-hooks/pre-commit', './.git/hooks/pre-commit');
    $fs->touch('./.git/hooks/foo');
    $event->getIO()->write('Symlinked git hooks.');
  }

}
