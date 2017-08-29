<?php

namespace Drupal\lightning;

use Symfony\Component\Console\Style\OutputStyle;

interface ConsoleAwareInterface {

  public function setIO(OutputStyle $io);

}
