<?php

namespace Phlox;

class AstPrinter implements Visitor
{
    public function print(Expr $expr)
    {
        return $expr->accept($this);
    }

    public function visitBinaryExpr($expr)
    {
        return $this->parenthesize(
            $expr->operator->lexeme,
            $expr->left,
            $expr->right
        );
    }

    public function visitGroupingExpr($expr)
    {
        return $this->parenthesize('group', $expr->expression);
    }

    public function visitLiteralExpr($expr)
    {
        if ($expr->value === null) {
            return 'nil';
        }

        return (string) $expr->value;
    }

    public function visitUnaryExpr($expr)
    {
        return $this->parenthesize($expr->operator->lexeme, $expr->right);
    }

    private function parenthesize(string $name, ...$exprs) : string
    {
        $resultString = "({$name}";

        foreach ($exprs as $expr) {
            $resultString .= " " . $expr->accept($this);
        }

        return $resultString . ")";
    }
}