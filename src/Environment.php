<?php

namespace Phlox;

class Environment
{
    public ?Environment $enclosing;
    protected array $values = [];

    public function __construct(Environment $enclosing = null)
    {
        $this->enclosing = $enclosing;
    }

    public function define(string $name, mixed $value)
    {
        $this->values[$name] = $value;
    }

    public function get(Token $name): mixed
    {
        if (array_key_exists($name->lexeme, $this->values)) {
            return $this->values[$name->lexeme];
        }

        if ($this->enclosing !== null) {
            return $this->enclosing->get($name);
        }

        throw new RuntimeError($name, "Undefined variable '" . $name->lexeme . "'.");
    }

    /**
     * @throws RuntimeError
     */
    public function assign(Token $name, $value): void
    {
        if (array_key_exists($name->lexeme, $this->values)) {
            $this->values[$name->lexeme] = $value;

            return;
        }

        if ($this->enclosing !== null) {
            $this->enclosing->assign($name, $value);
            return;
        }

        throw new RuntimeError($name, "Undefined variable '" . $name->lexeme . "'.");
    }

    public function getAt(mixed $distance, string $name): mixed
    {
        return $this->ancestor($distance)->values[$name];
    }

    private function ancestor(mixed $distance): self
    {
        $env = $this;
        for ($i = 0; $i < $distance; $i++) {
            $env = $env->enclosing;
        }

        return $env;
    }

    public function assignAt(mixed $distance, Token $name, $value)
    {
        $this->ancestor($distance)->values[$name->lexeme] = $value;
    }
}