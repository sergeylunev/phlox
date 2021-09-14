<?php

declare(strict_types=1);

namespace Phlox\Expr;

use Phlox\Expr;
use Phlox\Token;
use Phlox\Visitor;

class Binary extends Expr
{
    public Expr $left;
    public Token $operator;
    public Expr $right;

    public function __construct(Expr $left, Token $operator, Expr $right)
    {
        $this->left = $left;
        $this->operator = $operator;
        $this->right = $right;
    }
    public function accept(Visitor $visitor)
    {
        return $visitor->visitBinaryExpr($this);
    }
}