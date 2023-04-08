<?php

declare(strict_types=1);

namespace Phlox;

use Phlox\Expr\Assign;
use Phlox\Expr\Binary;
use Phlox\Expr\Call;
use Phlox\Expr\Get;
use Phlox\Expr\Grouping;
use Phlox\Expr\Literal;
use Phlox\Expr\Logical;
use Phlox\Expr\Unary;
use Phlox\Expr\Variable;

interface ExprVisitor
{
    public function visitAssignExpr(Assign $expr);

    public function visitBinaryExpr(Binary $expr);

    public function visitCallExpr(Call $expr);

    public function visitGetExpr(Get $expr);

    public function visitGroupingExpr(Grouping $expr);

    public function visitLiteralExpr(Literal $expr);

    public function visitLogicalExpr(Logical $expr);

    public function visitUnaryExpr(Unary $expr);

    public function visitVariableExpr(Variable $expr);

}
