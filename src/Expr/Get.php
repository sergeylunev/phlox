<?php

declare(strict_types=1);

namespace Phlox\Expr;

use Phlox\Expr;
use Phlox\ExprVisitor;
use Phlox\Token;

class Get extends Expr
{
    public Expr $object;
    public Token $name;

    public function __construct(Expr $object, Token $name)
    {
        $this->object = $object;
        $this->name = $name;
    }
    public function accept(ExprVisitor $visitor)
    {
        return $visitor->visitGetExpr($this);
    }
}
