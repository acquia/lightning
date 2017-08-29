<?php

namespace Drupal\lightning;

use phpDocumentor\Reflection\DocBlock;
use Symfony\Component\Console\Style\OutputStyle;

/**
 * Base class for interactive update plugins.
 */
abstract class UpdateBase implements ConsoleAwareInterface {

  /**
   * The console output driver.
   *
   * @var \Symfony\Component\Console\Style\OutputStyle
   */
  protected $io;

  /**
   * {@inheritdoc}
   */
  public function setIO(OutputStyle $io) {
    $this->io = $io;
  }

  protected function confirm($method) {
    $doc_comment = (new \ReflectionObject($this))
      ->getMethod($method)
      ->getDocComment();

    $doc_block = new DocBlock($doc_comment);
    $tags = $doc_block->getTagsByName('ask');

    if ($tags) {
      $question = str_replace(
        ["\r", "\n"],
        [NULL, ' '],
        reset($tags)->getContent()
      );

      $proceed = $this->io->confirm($question);
      if ($proceed) {
        $this->$method();
      }
    }
  }

}
