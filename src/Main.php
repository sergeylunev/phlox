<?php

declare(strict_types=1);

namespace Phlox;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Main extends Command {
    protected static $defaultName = 'app:main';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $debug = new Debug($output);

        $chunk = new Chunk([], 0);
        $chunk->writeChunk(OpCode::OP_RETURN);
        $debug->disassembleChunk($chunk, 'test chunk');
        $chunk->freeChunk();

        return Command::SUCCESS;
    }
}