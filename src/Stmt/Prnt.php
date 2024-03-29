<?php

declare(strict_types=1);

namespace Phlox\Stmt;

use Phlox\Stmt;
use Phlox\StmtVisitor;
use Phlox\Expr;

class Prnt extends Stmt
{
    public Expr $expression;

    public function __construct(Expr $expression)
    {
        $this->expression = $expression;
    }
    public function accept(StmtVisitor $visitor)
    {
        return $visitor->visitPrntStmt($this);
    }
}
