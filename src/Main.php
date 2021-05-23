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

        $chunk = new Chunk();

        $value = new Value();
        $value->value = 1.2;
        $constant = $chunk->addConstant($value);

        $chunk->writeChunk(OpCode::OP_CONSTANT, 123);
        $chunk->writeChunk($constant, 123);

        $value2 = new Value();
        $value2->value = 1000;
        $const = $chunk->addConstant($value2);

        $chunk->writeChunk(OpCode::OP_CONSTANT, 123);
        $chunk->writeChunk($const, 123);

        $chunk->writeChunk(OpCode::OP_RETURN, 124);
        $debug->disassembleChunk($chunk, 'test chunk');
        $chunk->freeChunk();

        return Command::SUCCESS;
    }
}