<?php

declare(strict_types=1);

namespace Phlox;

interface StmtVisitor
{
    public function visitExpressionStmt($stmt);

    public function visitPrntStmt($stmt);

}
