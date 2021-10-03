<?php

declare(strict_types=1);

namespace Phlox\Stmt;

use Phlox\Expr;
use Phlox\Stmt;
use Phlox\StmtVisitor;
use Phlox\Token;

class Vari extends Stmt
{
    public Token $name;
    public Expr $initializer;

    public function __construct(Token $name, Expr $initializer)
    {
        $this->name = $name;
        $this->initializer = $initializer;
    }
    public function accept(StmtVisitor $visitor)
    {
        return $visitor->visitVariStmt($this);
    }
}
