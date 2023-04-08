<?php

declare(strict_types=1);

namespace Phlox;

class PhloxClass implements PhloxCallable
{
    public function __construct(readonly public string $name)
    {
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function arity(): int
    {
        return 0;
    }

    public function call(Interpreter $interpreter, array $arguments)
    {
        $instance = new PhloxInstance($this);

        return $instance;
    }
}