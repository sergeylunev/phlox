<?php

declare(strict_types=1);

namespace Phlox;

class Chunk
{
    /** @var int[] */
    private array $code;

    private int $count;

    private ValueArray $constants;

    /** @var int[] */
    private array $lines;

    public function __construct()
    {
        $this->code = [];
        $this->count = 0;
        $this->constants = new ValueArray();
        $this->lines = [];
    }

    public function writeChunk(int $byte, int $line): void
    {
        $this->code[] = $byte;
        $this->lines[] = $line;
        $this->count++;
    }

    public function freeChunk(): Chunk
    {
        $this->count = 0;
        $this->code = [];

        return $this;
    }

    public function getCode(int $offset): int
    {
        return $this->code[$offset];
    }

    public function getCodes(): array
    {
        return $this->code;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function addConstant(Value $value): int
    {
        $this->constants->writeValueArray($value);

        return $this->constants->getCount() - 1;
    }

    public function getConstant(int $offset): Value
    {
        return $this->constants->getValue($offset);
    }

    public function getLines(): array
    {
        return $this->lines;
    }

    public function getLine(int $index): int
    {
        return $this->getLines()[$index];
    }

}
