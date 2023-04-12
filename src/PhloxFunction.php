<?php

namespace Phlox;

use Phlox\Stmt\Fun;

class PhloxFunction implements PhloxCallable
{
    public function __construct(
        private Fun         $declaration,
        private Environment $closure,
        private bool        $isInitializer
    )
    {
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
            if ($this->isInitializer) return $this->closure->getAt(0, "this");

            return $returnValue->value;
        }
        if ($this->isInitializer) {
            return $this->closure->getAt(0, "this");
        }
    }

    public function __toString(): string
    {
        return '<fn ' . $this->declaration->name->lexeme . '>';
    }

    public function bind(PhloxInstance $instance): PhloxFunction
    {
        $env = new Environment();
        $env->define("this", $instance);

        return new PhloxFunction($this->declaration, $env, $this->isInitializer);
    }
}