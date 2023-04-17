<?php

declare(strict_types=1);

namespace Phlox\Expr;

use Phlox\Expr;
use Phlox\ExprVisitor;
use Phlox\Token;

class Super extends Expr
{
    public Token $keyword;
    public Token $method;

    public function __construct(Token $keyword, Token $method)
    {
        $this->keyword = $keyword;
        $this->method = $method;
    }
    public function accept(ExprVisitor $visitor)
    {
        return $visitor->visitSuperExpr($this);
    }
}
