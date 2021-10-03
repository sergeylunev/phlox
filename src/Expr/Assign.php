<?php

declare(strict_types=1);

namespace Phlox\Expr;

use Phlox\Expr;
use Phlox\ExprVisitor;
use Phlox\Token;

class Assign extends Expr
{
    public Token $name;
    public Expr $value;

    public function __construct(Token $name, Expr $value)
    {
        $this->name = $name;
        $this->value = $value;
    }
    public function accept(ExprVisitor $visitor)
    {
        return $visitor->visitAssignExpr($this);
    }
}
