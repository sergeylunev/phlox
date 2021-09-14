<?php

declare(strict_types=1);

namespace Phlox\Expr;

use Phlox\Expr;
use Phlox\Token;
use Phlox\Visitor;

class Unary extends Expr
{
    public Token $operator;
    public Expr $right;

    public function __construct(Token $operator, Expr $right)
    {
        $this->operator = $operator;
        $this->right = $right;
    }
    public function accept(Visitor $visitor)
    {
        return $visitor->visitUnaryExpr($this);
    }
}
