#!/usr/bin/env php

<?php 

require __DIR__.'/vendor/autoload.php';

use Phlox\tools\AstGenerator;
use Phlox\tools\GenerateAstCommand;
use Symfony\Component\Console\Application;
use Phlox\Main;

$application = new Application();

try {
    $astGenerator = new AstGenerator(__DIR__ . '/output');
} catch (Exception $e) {
    echo $e->getMessage();

    die();
}

$application->add(new GenerateAstCommand($astGenerator));
$application->add(new Main());

$application->run();