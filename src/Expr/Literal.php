<?php

declare(strict_types=1);

namespace Phlox\Expr;

use Phlox\Expr;
use Phlox\ExprVisitor;

class Literal extends Expr
{
    public mixed $value;

    public function __construct(mixed $value)
    {
        $this->value = $value;
    }
    public function accept(ExprVisitor $visitor)
    {
        return $visitor->visitLiteralExpr($this);
    }
}
