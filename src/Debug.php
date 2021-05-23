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

    public function disassembleChunk(Chunk $chunk, string $name): void
    {
        $this->output->writeln(sprintf("== %s ==", $name));

        foreach ($chunk->getCodes() as $key => $value) {
            $this->disassembleInstruction($chunk, $key);
        }
    }

    public function disassembleInstruction(Chunk $chunk, int $offset): void
    {
        $this->output->write(sprintf("%04d ", $offset));

        $instruction = $chunk->getCode($offset);
        switch ($instruction) {
            case OpCode::OP_CONSTANT:
                $this->constantInstruction("OP_CONSTANT", $chunk, $offset); break;
            case OpCode::OP_RETURN:
                $this->simpleInstruction("OP_RETURN"); break;
            default:
                $this->output->writeln(sprintf("Unknown opcode %d", $instruction)); break;
        }
    }

    protected function simpleInstruction(string $name): void
    {
        $this->output->write(sprintf("%s\n", $name));
    }

    private function constantInstruction(string $name, Chunk $chunk, int $offset): void
    {
        $constant = $chunk->getCode($offset + 1);
        $this->output->write(sprintf("%-16s %4d '", $name, $constant));
        $value = $chunk->getConstant($constant);
        $this->output->writeln($value->printValue());
    }
}
