<?php

namespace Phlox\Native;

use Phlox\Interpreter;
use Phlox\PhloxCallable;

class Clock implements PhloxCallable
{
    public function arity(): int
    {
        return 0;
    }

    public function call(Interpreter $interpreter, array $arguments)
    {
        return time();
    }

    public function __toString(): string
    {
        return '<native fn>';
    }
}