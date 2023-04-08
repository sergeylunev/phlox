<?php

declare(strict_types=1);

namespace Phlox\Stmt;

use Phlox\Stmt;
use Phlox\StmtVisitor;
use Phlox\Token;

class Clas extends Stmt
{
    public Token $name;
    public array $methods;

    public function __construct(Token $name, array $methods)
    {
        $this->name = $name;
        $this->methods = $methods;
    }
    public function accept(StmtVisitor $visitor)
    {
        return $visitor->visitClasStmt($this);
    }
}
