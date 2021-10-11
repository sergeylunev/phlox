<?php

declare(strict_types=1);

namespace Phlox\Stmt;

use Phlox\Stmt;
use Phlox\StmtVisitor;
use Phlox\Token;

class Fun extends Stmt
{
    public Token $name;
    public array $params;
    public array $body;

    public function __construct(Token $name, array $params, array $body)
    {
        $this->name = $name;
        $this->params = $params;
        $this->body = $body;
    }
    public function accept(StmtVisitor $visitor)
    {
        return $visitor->visitFunStmt($this);
    }
}
