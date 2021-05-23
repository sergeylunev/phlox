#!/usr/bin/env php

<?php 

require __DIR__.'/vendor/autoload.php'; 

use Symfony\Component\Console\Application;
use Phlox\Main;

$application = new Application();

$application->add(new Main());

$application->run();