<?php

declare(strict_types=1);

namespace Phlox;

class PhloxInstance
{
    private array $fields = [];

    public function __construct(readonly private PhloxClass $klass)
    {
    }

    public function __toString(): string
    {
        return $this->klass->name . " instance";
    }

    public function get(Token $name)
    {
        if (array_key_exists($name->lexeme, $this->fields)) {
            return $this->fields[$name->lexeme];
        }

        $method = $this->klass->findMethod($name->lexeme);
        if ($method) return $method->bind($this);

        throw new RuntimeError($name,
            "Undefined property '" . $name->lexeme . "'.");
    }

    public function set(Token $name, $value)
    {
        $this->fields[$name->lexeme] = $value;
    }
}