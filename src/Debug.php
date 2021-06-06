<?php

declare(strict_types=1);

namespace Phlox;

use Symfony\Component\Console\Output\OutputInterface;

class Debug
{
    private OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function writeln(string $message): void
    {
        $this->output->writeln($message);
    }

    public function write(string $message): void
    {
        $this->output->write($message);
    }

    public function disassembleChunk(Chunk $chunk, string $name): void
    {
        $this->output->writeln(sprintf("== %s ==", $name));

        for ($offset = 0; $offset < $chunk->getCount();) {
            $offset = $this->disassembleInstruction($chunk, $offset);
        }
    }

    public function disassembleInstruction(Chunk $chunk, int $offset): int
    {
        $this->output->write(sprintf("%04d ", $offset));

        if (
            $offset > 0
            && $chunk->getLine($offset) === $chunk->getLine($offset - 1)
        ) {
            $this->output->write("   | ");
        } else {
            $this->output->write(sprintf("%4d ", $chunk->getLine($offset)));
        }

        $instruction = $chunk->getCode($offset);
        switch ($instruction) {
            case OpCode::OP_CONSTANT:
                return $this->constantInstruction("OP_CONSTANT", $chunk, $offset);
            case OpCode::OP_NEGATE:
                return $this->simpleInstruction("OP_NEGATE", $offset);
            case OpCode::OP_RETURN:
                return $this->simpleInstruction("OP_RETURN", $offset);
            default:
                $this->output->writeln(sprintf("Unknown opcode %d", $instruction));
                return $offset + 1;
        }
    }

    protected function simpleInstruction(string $name, int $offset): int
    {
        $this->output->write(sprintf("%s\n", $name));

        return $offset + 1;
    }

    private function constantInstruction(string $name, Chunk $chunk, int $offset): int
    {
        $constant = $chunk->getCode($offset + 1);
        $this->output->write(sprintf("%-16s %4d '", $name, $constant));
        $value = $chunk->getConstant($constant);
        $this->output->writeln($value->printValue() . "'");

        return $offset + 2;
    }
}
