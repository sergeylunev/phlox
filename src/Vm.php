<?php

declare(strict_types=1);

namespace Phlox;

class Vm
{
    private array $ip;
    private Chunk $chunk;
    private bool $withDebug;
    private Debug $debug;
    /** @var Value[] */
    private array $stack = [];

    public function __construct(bool $withDebug, Debug $debug)
    {
        $this->withDebug = $withDebug;
        $this->debug = $debug;
    }

    public function interpret(Chunk $chunk)
    {
        $this->chunk = $chunk;
        $this->ip = array_keys($this->chunk->getCodes());

        return $this->run();
    }

    private function run(): int
    {
        while (true) {
            if ($this->withDebug) {
                $this->debug->write("          ");
                foreach ($this->stack as $value) {
                    $this->debug->write("[ ");
                    $this->debug->write($value->printValue());
                    $this->debug->write(" ]");
                }
                $this->debug->write("\n");

                $this->debug->disassembleInstruction($this->chunk, $this->currentByte());
            }

            $instruction = $this->chunk->getCode($this->readByte());
            switch ($instruction) {
                case OpCode::OP_CONSTANT:
                    $constant = $this->readConstant();
                    $this->push($constant);
                    break;
                case OpCode::OP_RETURN:
                    $this->debug->writeln($this->pop()->printValue());
                    return IntereptCode::INTERPRET_OK;
            }
        }
    }

    protected function readByte(): int
    {
        $result = current($this->ip);
        next($this->ip);

        return $result;
    }

    protected function currentByte(): int
    {
        return current($this->ip);
    }

    private function readConstant(): Value
    {
        return $this->chunk->getConstant($this->chunk->getCode($this->readByte()));
    }

    public function push(Value $value)
    {
        $this->stack[] = $value;
    }

    public function pop(): Value
    {
        return array_pop($this->stack);
    }
}
