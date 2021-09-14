<?php

declare(strict_types=1);

namespace Phlox\Expr;

use Phlox\Expr;
use Phlox\Visitor;

class Grouping extends Expr
{
    public Expr $expression;

    public function __construct(Expr $expression)
    {
        $this->expression = $expression;
    }
    public function accept(Visitor $visitor)
    {
        return $visitor->visitGroupingExpr($this);
    }
}
