<?php

declare(strict_types=1);

namespace Phlox\Stmt;

use Phlox\Stmt;
use Phlox\StmtVisitor;
use Phlox\Expr;
use Phlox\Token;

class Rtrn extends Stmt
{
    public Token $keyword;
    public Expr $value;

    public function __construct(Token $keyword, Expr $value)
    {
        $this->keyword = $keyword;
        $this->value = $value;
    }
    public function accept(StmtVisitor $visitor)
    {
        return $visitor->visitRtrnStmt($this);
    }
}
