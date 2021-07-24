<?php

declare(strict_types=1);

namespace Phlox\tools;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateAstCommand extends Command
{
    protected const OPTION_OUTPUT = 'output';

    protected static $defaultName = 'tools:generate:ast';
    private AstGenerator $generator;

    public function __construct(AstGenerator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->generator->generate();

        return Command::SUCCESS;
    }
}
