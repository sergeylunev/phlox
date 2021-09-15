<?php

namespace Phlox;

use Symfony\Component\Console\Output\OutputInterface;

class Interpreter implements Visitor
{
    private OutputInterface $output;
    private Phlox $phlox;

    public function __construct(OutputInterface $output, Phlox $phlox)
    {
        $this->output = $output;
        $this->phlox = $phlox;
    }

    public function interpret(Expr $expression): void
    {
        try {
            $value = $this->evaluate($expression);
            $this->output->writeln($this->stringify($value));
        } catch (RuntimeError $exception) {
            $this->phlox->runtimeError($exception);
        }
    }
    
    /**
     * @throws RuntimeError
     */
    public function visitBinaryExpr($expr)
    {
        $left = $this->evaluate($expr->left);
        $right = $this->evaluate($expr->right);

        switch ($expr->operator->tokenType) {
            case TokenType::TOKEN_GREATER:
                $this->checkNumberOperands($expr->operator, $left, $right);
                return $left > $right;
            case TokenType::TOKEN_GREATER_EQUAL:
                $this->checkNumberOperands($expr->operator, $left, $right);
                return $left >= $right;
            case TokenType::TOKEN_LESS:
                $this->checkNumberOperands($expr->operator, $left, $right);
                return $left < $right;
            case TokenType::TOKEN_LESS_EQUAL:
                $this->checkNumberOperands($expr->operator, $left, $right);
                return $left <= $right;
            case TokenType::TOKEN_BANG_EQUAL:
                return !$this->isEqual($left, $right);
            case TokenType::TOKEN_EQUAL_EQUAL:
                return $this->isEqual($left, $right);
            case TokenType::TOKEN_MINUS:
                $this->checkNumberOperands($expr->operator, $left, $right);
                return (double)$left - (double)$right;
            case TokenType::TOKEN_SLASH:
                $this->checkNumberOperands($expr->operator, $left, $right);
                return (double)$left / (double)$right;
            case TokenType::TOKEN_STAR:
                $this->checkNumberOperands($expr->operator, $left, $right);
                return (double)$left * (double)$right;
            case TokenType::TOKEN_PLUS:
                if (is_double($left) && is_double($right)) {
                    return (double)$left + (double)$right;
                }
                if (is_string($left) && is_string($right)) {
                    return $left . $right;
                }

                throw new RuntimeError($expr->operator, "Operands must be two numbers or two strings.");
        }

        return null;
    }

    public function visitGroupingExpr($expr)
    {
        return $this->evaluate($expr->expression);
    }

    public function visitLiteralExpr($expr)
    {
        return $expr->value;
    }

    /**
     * @throws RuntimeError
     */
    public function visitUnaryExpr($expr): mixed
    {
        $right = $this->evaluate($expr->right);

        switch ($expr->operator->type) {
            case TokenType::TOKEN_BANG:
                return !$this->isTruthy($right);
            case TokenType::TOKEN_MINUS:
                $this->checkNumberOperand($expr->operator, $right);
                return -(double)$right;
        }

        return null;
    }

    private function evaluate(Expr $expression)
    {
        return $expression->accept($this);
    }

    private function isTruthy($object): bool
    {
        if ($object === null) {
            return false;
        }

        if (is_bool($object)) {
            return $object;
        }

        return true;
    }

    private function isEqual($a, $b): bool
    {
        if ($a === null && $b === null) return true;
        if ($a === null) return false;

        return $a === $b;
    }

    /**
     * @throws RuntimeError
     */
    private function checkNumberOperand(Token $operator, $operand)
    {
        if (is_double($operand)) {
            return;
        }

        throw new RuntimeError($operator, "Operand must be a number.");
    }

    /**
     * @throws RuntimeError
     */
    private function checkNumberOperands(Token $operator, $left, $right)
    {
        if (is_double($left) && is_double($right)) {
            return;
        }

        throw new RuntimeError($operator, "Operands must be numbers.");
    }

    private function stringify(mixed $value): string
    {
        if ($value === null) return 'nil';

        return $value;
    }
}