<?php

declare(strict_types=1);

namespace Phlox\Expr;

use Phlox\Expr;
use Phlox\ExprVisitor;
use Phlox\Token;

class Variable extends Expr
{
    public Token $name;

    public function __construct(Token $name)
    {
        $this->name = $name;
    }
    public function accept(ExprVisitor $visitor)
    {
        return $visitor->visitVariableExpr($this);
    }
}
