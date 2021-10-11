<?php

namespace Phlox;

interface PhloxCallable
{
    public function arity(): int;

    public function call(Interpreter $interpreter, array $arguments);
}