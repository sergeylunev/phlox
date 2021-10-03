<?php

declare(strict_types=1);

namespace Phlox;

interface ExprVisitor
{
    public function visitAssignExpr($expr);

    public function visitBinaryExpr($expr);

    public function visitGroupingExpr($expr);

    public function visitLiteralExpr($expr);

    public function visitUnaryExpr($expr);

    public function visitVariableExpr($expr);

}
