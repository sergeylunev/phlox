<?php

namespace Phlox;

use RuntimeException;

class ReturnValue extends RuntimeException
{
    public $value;

    public function __construct(mixed $value)
    {
        parent::__construct();

        $this->value = $value;
    }
}