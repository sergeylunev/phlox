<?php

declare(strict_types=1);

namespace Phlox\Expr;

use Phlox\Expr;
use Phlox\ExprVisitor;
use Phlox\Token;

class Thus extends Expr
{
    public Token $keyword;

    public function __construct(Token $keyword)
    {
        $this->keyword = $keyword;
    }
    public function accept(ExprVisitor $visitor)
    {
        return $visitor->visitThusExpr($this);
    }
}
