<?php

declare(strict_types=1);

namespace Phlox\Stmt;

use Phlox\Expr;
use Phlox\Stmt;
use Phlox\StmtVisitor;

class Fi extends Stmt
{
    public  Expr $condition;
    public Stmt $thenBranch;
    public Stmt $elseBranch;

    public function __construct(Expr $condition, Stmt $thenBranch, Stmt $elseBranch)
    {
        $this->condition = $condition;
        $this->thenBranch = $thenBranch;
        $this->elseBranch = $elseBranch;
    }
    public function accept(StmtVisitor $visitor)
    {
        return $visitor->visitFiStmt($this);
    }
}
