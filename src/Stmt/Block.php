<?php

declare(strict_types=1);

namespace Phlox\Stmt;

use Phlox\Stmt;
use Phlox\StmtVisitor;

class Block extends Stmt
{
    public array $statements;

    public function __construct(array $statements)
    {
        $this->statements = $statements;
    }
    public function accept(StmtVisitor $visitor)
    {
        return $visitor->visitBlockStmt($this);
    }
}
