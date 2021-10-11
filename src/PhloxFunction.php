<?php

namespace Phlox;

use Phlox\Stmt\Fun;

class PhloxFunction implements PhloxCallable
{
    private Fun $declaration;
    private Environment $closure;

    public function __construct(Fun $declaration, Environment $closure)
    {
        $this->declaration = $declaration;
        $this->closure = $closure;
    }

    public function arity(): int
    {
        return count($this->declaration->params);
    }

    public function call(Interpreter $interpreter, array $arguments)
    {
        $environment = new Environment($this->closure);
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