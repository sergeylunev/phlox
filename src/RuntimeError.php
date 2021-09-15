<?php

namespace Phlox;

use Throwable;

class RuntimeError extends \Exception
{
    public Token $token;

    public function __construct(Token $token, string $message)
    {
        parent::__construct($message);

        $this->token = $token;
    }
}