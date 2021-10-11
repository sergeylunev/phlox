<?php

namespace Phlox;

use Phlox\Stmt\Fun;

class PhloxFunction implements PhloxCallable
{
    private Fun $declaration;

    public function __construct(Fun $declaration)
    {
        $this->declaration = $declaration;
    }

    public function arity(): int
    {
        return count($this->declaration->params);
    }

    public function call(Interpreter $interpreter, array $arguments)
    {
        $environment = new Environment($interpreter->globals);
        for ($i = 0; $i < count($this->declaration->params); $i++) {
            $environment->define(
                $this->declaration->params[$i]->lexeme,
                $arguments[$i]
            );
        }

        try {
            $interpreter->executeBlock($this->declaration->body, $environment);
        } catch (ReturnValue $returnValue) {
            return $returnValue->value;
        }

    }

    public function __toString(): string
    {
        return '<fn ' . $this->declaration->name->lexeme . '>';
    }
}