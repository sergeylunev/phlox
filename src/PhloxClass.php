<?php

declare(strict_types=1);

namespace Phlox;

class PhloxClass implements PhloxCallable
{
    public function __construct(
        readonly public string $name,
        readonly public array $methods
    )
    {
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function arity(): int
    {
        $initializer = $this->findMethod("init");
        if ($initializer === null) {
            return 0;
        }

        return $initializer->arity();
    }

    public function call(Interpreter $interpreter, array $arguments)
    {
        $instance = new PhloxInstance($this);
        $initializer = $this->findMethod("init");
        if ($initializer) {
            $initializer->bind($instance)->call($interpreter, $arguments);
        }

        return $instance;
    }

    public function findMethod(string $name): ?PhloxFunction
    {
        if (array_key_exists($name, $this->methods)) {
            return $this->methods[$name];
        }

        return null;
    }
}