<?php

declare(strict_types=1);

namespace Phlox;

class Chunk
{
    /** @var int[] */
    private array $code;

    private int $count;

    private ValueArray $constants;

    public function __construct(array $code, int $count)
    {
        $this->code = $code;
        $this->count = $count;

        $this->constants = new ValueArray();
    }

    public function writeChunk(int $byte): void
    {
        $this->code[] = $byte;
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
}
