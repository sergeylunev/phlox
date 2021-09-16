<?php

declare(strict_types=1);

namespace Phlox\Expr;

use Phlox\Expr;
use Phlox\ExprVisitor;
use Phlox\Token;

class Unary extends Expr
{
    public Token $operator;
    public Expr $right;

    public function __construct(Token $operator, Expr $right)
    {
        $this->operator = $operator;
        $this->right = $right;
    }
    public function accept(ExprVisitor $visitor)
    {
        return $visitor->visitUnaryExpr($this);
    }
}
