<?php

declare(strict_types=1);

namespace Phlox\Stmt;

use Phlox\Expr;
use Phlox\Stmt;
use Phlox\StmtVisitor;

class Whle extends Stmt
{
    public Expr $condition;
    public Stmt $body;

    public function __construct(Expr $condition, Stmt $body)
    {
        $this->condition = $condition;
        $this->body = $body;
    }
    public function accept(StmtVisitor $visitor)
    {
        return $visitor->visitWhleStmt($this);
    }
}
