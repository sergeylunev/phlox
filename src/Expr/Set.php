<?php

declare(strict_types=1);

namespace Phlox\Expr;

use Phlox\Expr;
use Phlox\ExprVisitor;
use Phlox\Token;

class Set extends Expr
{
    public Expr $object;
    public Token $name;
    public Expr $value;

    public function __construct(Expr $object, Token $name, Expr $value)
    {
        $this->object = $object;
        $this->name = $name;
        $this->value = $value;
    }
    public function accept(ExprVisitor $visitor)
    {
        return $visitor->visitSetExpr($this);
    }
}
