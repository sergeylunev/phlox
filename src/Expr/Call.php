<?php

declare(strict_types=1);

namespace Phlox\Expr;

use Phlox\Expr;
use Phlox\ExprVisitor;
use Phlox\Token;

class Call extends Expr
{
    public Expr $callee;
    public Token $paren;
    public array $arguments;

    public function __construct(Expr $callee, Token $paren, array $arguments)
    {
        $this->callee = $callee;
        $this->paren = $paren;
        $this->arguments = $arguments;
    }
    public function accept(ExprVisitor $visitor)
    {
        return $visitor->visitCallExpr($this);
    }
}
