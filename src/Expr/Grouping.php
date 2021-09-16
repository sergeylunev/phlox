<?php

declare(strict_types=1);

namespace Phlox\Expr;

use Phlox\Expr;
use Phlox\ExprVisitor;

class Grouping extends Expr
{
    public Expr $expression;

    public function __construct(Expr $expression)
    {
        $this->expression = $expression;
    }
    public function accept(ExprVisitor $visitor)
    {
        return $visitor->visitGroupingExpr($this);
    }
}
