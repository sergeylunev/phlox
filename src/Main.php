<?php

declare(strict_types=1);

namespace Phlox;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Main extends Command
{
    protected const DEBUG_MODE = 'debug';
    
    protected static $defaultName = 'app:main';

    protected function configure()
    {
        $this->addOption(
            'debug',
            'd',
            InputOption::VALUE_NONE,
            'Enable debug mode'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $phlox = new Phlox($output);

        $phlox->runString('1 + 2');

        return Command::SUCCESS;
    }
}