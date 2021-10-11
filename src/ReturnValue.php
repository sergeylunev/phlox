<?php

namespace Phlox;

use Throwable;

class ReturnValue extends \RuntimeException
{
    public $value;

    public function __construct(mixed $value)
    {
        parent::__construct(null, null, null);

        $this->value = $value;
    }
}