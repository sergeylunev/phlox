<?php

namespace Phlox;

class Environment
{
    protected array $values = [];
    public ?Environment $enclosing;

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

        throw new RuntimeError($name,"Undefined variable '" . $name->lexeme . "'.");
    }
}