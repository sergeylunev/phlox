<?php

namespace Phlox;

use http\Env;
use Phlox\Expr\Assign;
use Phlox\Stmt\Expression;
use Phlox\Stmt\Prnt;
use Symfony\Component\Console\Output\OutputInterface;

class Interpreter implements ExprVisitor, StmtVisitor
{
    private OutputInterface $output;
    private Phlox $phlox;
    private Environment $environment;

    public function __construct(OutputInterface $output, Phlox $phlox)
    {
        $this->output = $output;
        $this->phlox = $phlox;

        $this->environment = new Environment();
    }

    /**
     * @param Stmt[] $statements
     */
    public function interpret(array $statements): void
    {
        try {
            foreach ($statements as $statement) {
                $this->execute($statement);
            }
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

    /**
     * @param Expression $stmt
     */
    public function visitExpressionStmt($stmt): void
    {
        $this->evaluate($stmt->expression);
    }

    /**
     * @param Prnt $stmt
     */
    public function visitPrntStmt($stmt): void
    {
        $value = $this->evaluate($stmt->expression);

        echo $this->stringify($value);
    }

    private function execute(Stmt $statement): void
    {
        $statement->accept($this);
    }

    private function executeBlock(array $statements, Environment $environment): void
    {
        $previous = $this->environment;

        try {
            $this->environment = $environment;

            foreach ($statements as $statement) {
                $this->execute($statement);
            }
        } finally {
            $this->environment = $previous;
        }
    }

    public function visitVariableExpr($expr)
    {
        return $this->environment->get($expr->name);
    }

    /**
     * @param Stmt $stmt
     */
    public function visitVariStmt($stmt): void
    {
        $value = null;
        if ($stmt->initializer !== null) {
            $value = $this->evaluate($stmt->initializer);
        }

        $this->environment->define($stmt->name->lexeme, $value);
    }

    /**
     * @param Assign $expr
     */
    public function visitAssignExpr($expr): mixed
    {
        $value = $this->evaluate($expr->value);
        $this->environment->assign($expr->name, $value);

        return $value;
    }

    public function visitBlockStmt($stmt): void
    {
        $this->executeBlock($stmt->statements, new Environment($this->environment));
    }
}