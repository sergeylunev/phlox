<?php

declare(strict_types=1);

namespace Phlox;

class ValueArray
{
    protected int $count = 0;

    /** @var Value[] */
    protected array $values = [];

    public function getCount(): int
    {
        return $this->count;
    }

    /** @return Value[] */
    public function getValues(): array
    {
        return $this->values;
    }

    public function getValue(int $index): Value
    {
        return $this->getValues()[$index];
    }

    protected function addValue(Value $value)
    {
        $this->values[] = $value;
    }

    public function writeValueArray(Value $value): void
    {
        $this->addValue($value);
        $this->count++;
    }

    public function freeValueArray(): void
    {
        $this->values = [];
        $this->count = 0;
    }
}
