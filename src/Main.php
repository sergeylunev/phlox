<?php

declare(strict_types=1);

namespace Phlox;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Main extends Command
{
    protected const DEBUG_MODE = 'debug';
    protected const ARGUMENT_FILE = 'file';

    
    protected static $defaultName = 'app:main';

    protected function configure()
    {
        $this->addOption(
            'debug',
            'd',
            InputOption::VALUE_NONE,
            'Enable debug mode'
        );

        $this->addArgument(self::ARGUMENT_FILE, InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $phlox = new Phlox($output);

        if (($filePath = $input->getArgument(self::ARGUMENT_FILE)) !== null) {
            $phlox->runFile($filePath);
        } else {
            $phlox->runString('print "one";');
        }

        return Command::SUCCESS;
    }
}