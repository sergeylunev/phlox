<?php

namespace Phlox;

use Phlox\Expr\Assign;
use Phlox\Expr\Call;
use Phlox\Expr\Logical;
use Phlox\Expr\Variable;
use Phlox\Native\Clock;
use Phlox\Stmt\Expression;
use Phlox\Stmt\Fi;
use Phlox\Stmt\Fun;
use Phlox\Stmt\Prnt;
use Phlox\Stmt\Rtrn;
use Phlox\Stmt\Whle;
use SplObjectStorage;
use Symfony\Component\Console\Output\OutputInterface;

class Interpreter implements ExprVisitor, StmtVisitor
{
    public Environment $globals;
    private OutputInterface $output;
    private Phlox $phlox;
    private Environment $environment;
    private SplObjectStorage $locals;

    public function __construct(OutputInterface $output, Phlox $phlox)
    {
        $this->output = $output;
        $this->phlox = $phlox;

        $this->globals = new Environment();
        $this->environment = $this->globals;

        $this->locals = new SplObjectStorage();

        $this->_init();
    }

    private function _init(): void
    {
        $this->globals->define('clock', new Clock());
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

    private function execute(Stmt $statement): void
    {
        $statement->accept($this);
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

    private function evaluate(Expr $expression)
    {
        return $expression->accept($this);
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

    private function isEqual($a, $b): bool
    {
        if ($a === null && $b === null) return true;
        if ($a === null) return false;

        return $a === $b;
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

    private function stringify(mixed $value): string
    {
        if ($value === null) return 'nil';

        return $value;
    }

    /**
     * @param Variable $expr
     * @return mixed
     * @throws RuntimeError
     */
    public function visitVariableExpr($expr)
    {
        return $this->lookUpVariable($expr->name, $expr);
    }

    private function lookUpVariable(Token $name, Variable $expr)
    {
        try {
            $distance = $this->locals[$expr];
        } catch (\Exception $exception) {
            $distance = null;
        }

        if ($distance !== null) {
            return $this->environment->getAt($distance, $name->lexeme);
        } else {
            return $this->globals->get($name);
        }
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

        $distance = $this->locals[$expr];
        if ($distance !== null) {
            $this->environment->assignAt($distance, $expr->name, $value);
        } else {
            $this->globals->assign($expr->name, $value);
        }

        return $value;
    }

    public function visitBlockStmt($stmt): void
    {
        $this->executeBlock($stmt->statements, new Environment($this->environment));
    }

    public function executeBlock(array $statements, Environment $environment): void
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

    /**
     * @param Fi $stmt
     */
    public function visitFiStmt($stmt): void
    {
        if ($this->isTruthy($this->evaluate($stmt->condition))) {
            $this->execute($stmt->thenBranch);
        } elseif ($stmt->elseBranch !== null) {
            $this->execute($stmt->elseBranch);
        }
    }

    /**
     * @param Logical $expr
     */
    public function visitLogicalExpr($expr)
    {
        $left = $this->evaluate($expr->left);

        if ($expr->operator->tokenType === TokenType::TOKEN_OR) {
            if ($this->isTruthy($left)) {
                return $left;
            }
        } else {
            if (!$this->isTruthy($left)) {
                return $left;
            }
        }

        return $this->evaluate($expr->right);
    }

    /**
     * @param Whle $stmt
     */
    public function visitWhleStmt($stmt): void
    {
        while ($this->isTruthy($this->evaluate($stmt->condition))) {
            $this->execute($stmt->body);
        }
    }

    /**
     * @param Call $expr
     *
     * @throws RuntimeError
     */
    public function visitCallExpr($expr)
    {
        $callee = $this->evaluate($expr->callee);

        $arguments = [];
        /** @var Expr $argument */
        foreach ($expr->arguments as $argument) {
            $arguments[] = $this->evaluate($argument);
        }

        if (!$callee instanceof PhloxCallable) {
            throw new RuntimeError($expr->paren, "Can only call functions and classes.");
        }

        $function = $callee;

        if ($argumentsCount = count($arguments) != $function->arity()) {
            throw new RuntimeError(
                $expr->paren,
                "Expected {$function->arity()} arguments but got {$argumentsCount}."
            );
        }

        return $function->call($this, $arguments);
    }

    /**
     * @param Fun $stmt
     */
    public function visitFunStmt($stmt): void
    {
        $funct = new PhloxFunction($stmt, $this->environment);
        $this->environment->define($stmt->name->lexeme, $funct);
    }

    /**
     * @param Rtrn $stmt
     */
    public function visitRtrnStmt($stmt)
    {
        $value = null;
        if ($stmt->value != null) {
            $value = $this->evaluate($stmt->value);
        }

        throw new ReturnValue($value);
    }

    public function resolve(Expr $expr, int $depth): void
    {
        $this->locals[$expr] = $depth;
    }
}