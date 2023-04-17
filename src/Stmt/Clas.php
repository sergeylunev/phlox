<?php

declare(strict_types=1);

namespace Phlox\Stmt;

use Phlox\Stmt;
use Phlox\StmtVisitor;
use Phlox\Token;
use Phlox\Expr\Variable;

class Clas extends Stmt
{
    public Token $name;
    public ?Variable $superclass;
    public array $methods;

    public function __construct(Token $name, ?Variable $superclass, array $methods)
    {
        $this->name = $name;
        $this->superclass = $superclass;
        $this->methods = $methods;
    }
    public function accept(StmtVisitor $visitor)
    {
        return $visitor->visitClasStmt($this);
    }
}
