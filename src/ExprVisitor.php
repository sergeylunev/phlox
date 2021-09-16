<?php

declare(strict_types=1);

namespace Phlox;

interface ExprVisitor
{
    public function visitBinaryExpr($expr);

    public function visitGroupingExpr($expr);

    public function visitLiteralExpr($expr);

    public function visitUnaryExpr($expr);

}
