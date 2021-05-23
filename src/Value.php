<?php

declare(strict_types=1);

namespace Phlox;

class Value
{
    public $value;

    public function printValue(): string
    {
        return sprintf("%g", $this->value);
    }
}
