<?php

declare(strict_types=1);

namespace Phlox\Stmt;

use Phlox\Expr;
use Phlox\Stmt;
use Phlox\StmtVisitor;

class Expression extends Stmt
{
    public Expr $expression;

    public function __construct(Expr $expression)
    {
        $this->expression = $expression;
    }
    public function accept(StmtVisitor $visitor)
    {
        return $visitor->visitExpressionStmt($this);
    }
}
