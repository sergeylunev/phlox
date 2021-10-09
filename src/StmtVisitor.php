<?php

declare(strict_types=1);

namespace Phlox;

interface StmtVisitor
{
    public function visitBlockStmt($stmt);

    public function visitExpressionStmt($stmt);

    public function visitFiStmt($stmt);

    public function visitPrntStmt($stmt);

    public function visitVariStmt($stmt);

    public function visitWhleStmt($stmt);

}
