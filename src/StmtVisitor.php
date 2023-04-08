<?php

declare(strict_types=1);

namespace Phlox;

use Phlox\Stmt\Block;
use Phlox\Stmt\Clas;
use Phlox\Stmt\Expression;
use Phlox\Stmt\Fun;
use Phlox\Stmt\Fi;
use Phlox\Stmt\Prnt;
use Phlox\Stmt\Rtrn;
use Phlox\Stmt\Vari;
use Phlox\Stmt\Whle;

interface StmtVisitor
{
    public function visitBlockStmt(Block $stmt);

    public function visitClasStmt(Clas $stmt);

    public function visitExpressionStmt(Expression $stmt);

    public function visitFunStmt(Fun $stmt);

    public function visitFiStmt(Fi $stmt);

    public function visitPrntStmt(Prnt $stmt);

    public function visitRtrnStmt(Rtrn $stmt);

    public function visitVariStmt(Vari $stmt);

    public function visitWhleStmt(Whle $stmt);

}
