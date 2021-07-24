<?php

declare(strict_types=1);

namespace Phlox\Expr;

use Phlox\Expr;

class Literal extends Expr
{
    public Object $value;

    public function __construct(object $value)
    {
        $this->value = $value;
    }
}
