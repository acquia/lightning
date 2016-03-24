#!/usr/bin/env php
<?php

use Acquia\Lightning\Command\PackageCommand;
use Symfony\Component\Console\Application;

require __DIR__ . '/vendor/autoload.php';

$application = new Application();
$application->add(new PackageCommand());
$application->run();
