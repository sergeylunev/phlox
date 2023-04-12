<?php

declare(strict_types=1);

namespace Phlox;

use Phlox\Expr\Assign;
use Phlox\Expr\Binary;
use Phlox\Expr\Call;
use Phlox\Expr\Get;
use Phlox\Expr\Grouping;
use Phlox\Expr\Logical;
use Phlox\Expr\Set;
use Phlox\Expr\Thus;
use Phlox\Expr\Unary;
use Phlox\Expr\Variable;
use Phlox\Stmt\Block;
use Phlox\Stmt\Clas;
use Phlox\Stmt\Expression;
use Phlox\Stmt\Fi;
use Phlox\Stmt\Fun;
use Phlox\Stmt\Prnt;
use Phlox\Stmt\Rtrn;
use Phlox\Stmt\Vari;
use Phlox\Stmt\Whle;

class Resolver implements ExprVisitor, StmtVisitor
{
    private array $scopes = [];
    private FunctionType $currentFunction = FunctionType::NONE;
    private ClassType $currentClass = ClassType::NONE;

    public function __construct(
        readonly private Phlox       $phlox,
        readonly private Interpreter $interpreter
    )
    {
    }

    /**
     * @param Assign $expr
     * @return void
     */
    public function visitAssignExpr($expr): void
    {
        $this->resolveExpr($expr->value);
        $this->resolveLocal($expr, $expr->name);
    }

    private function resolveExpr(Expr $expr): void
    {
        $expr->accept($this);
    }

    private function resolveLocal(Expr $expr, Token $name)
    {
        for ($i = count($this->scopes) - 1; $i >= 0; $i--) {
            if (array_key_exists($name->lexeme, $this->scopes[$i])) {
                $this->interpreter->resolve($expr, count($this->scopes) - 1 - $i);
                return;
            }
        }
    }

    /**
     * @param Stmt[] $statements
     * @return void
     */
    public function resolve(array $statements): void
    {
        foreach ($statements as $statement) {
            $this->resolveStmt($statement);
        }
    }

    private function resolveStmt(Stmt $statement): void
    {
        $statement->accept($this);
    }

    /**
     * @param Binary $expr
     * @return void
     */
    public function visitBinaryExpr($expr): void
    {
        $this->resolveExpr($expr->left);
        $this->resolveExpr($expr->right);
    }

    /**
     * @param Call $expr
     * @return void
     */
    public function visitCallExpr($expr): void
    {
        $this->resolveExpr($expr->callee);

        /** @var Expr $argument */
        foreach ($expr->arguments as $argument) {
            $this->resolveExpr($argument);
        }
    }

    /**
     * @param Grouping $expr
     * @return void
     */
    public function visitGroupingExpr($expr): void
    {
        $this->resolveExpr($expr->expression);
    }

    public function visitLiteralExpr($expr): void
    {
    }

    /**
     * @param Logical $expr
     * @return void
     */
    public function visitLogicalExpr($expr): void
    {
        $this->resolveExpr($expr->left);
        $this->resolveExpr($expr->right);
    }

    /**
     * @param Unary $expr
     * @return void
     */
    public function visitUnaryExpr($expr): void
    {
        $this->resolveExpr($expr->right);
    }

    /**
     * @param Variable $expr
     * @return void
     */
    public function visitVariableExpr($expr): void
    {
        if (!empty($this->scopes)
            && (array_key_exists($expr->name->lexeme, $this->scopes[count($this->scopes) - 1])
                && $this->scopes[count($this->scopes) - 1][$expr->name->lexeme] === false
            )
        ) {
            $this->phlox->tokenTypeError($expr->name, "Can't read local variable in its own initializer.");
        }

        $this->resolveLocal($expr, $expr->name);
    }

    /**
     * @param Block $stmt
     * @return void
     */
    public function visitBlockStmt($stmt): void
    {
        $this->beginScope();
        $this->resolve($stmt->statements);
        $this->endScope();
    }

    private function beginScope(): void
    {
        $this->scopes[] = [];
    }

    private function endScope(): void
    {
        array_pop($this->scopes);
    }

    /**
     * @param Expression $stmt
     * @return void
     */
    public function visitExpressionStmt($stmt): void
    {
        $this->resolveExpr($stmt->expression);
    }

    /**
     * @param Fun $stmt
     * @return void
     */
    public function visitFunStmt($stmt)
    {
        $this->declare($stmt->name);
        $this->define($stmt->name);

        $this->resolveFunction($stmt, FunctionType::FUNC);
    }

    private function declare(Token $name): void
    {
        if (empty($this->scopes)) return;

        $scope = $this->scopes[count($this->scopes) - 1];
        if (array_key_exists($name->lexeme, $scope)) {
            $this->phlox->tokenTypeError($name, "Already a variable with this name in this scope.");
        }
        $scope[$name->lexeme] = false;
    }

    private function define(Token $name): void
    {
        if (empty($this->scopes)) return;

        $this->scopes[count($this->scopes) - 1][$name->lexeme] = true;
    }

    private function resolveFunction(Fun $stmt, FunctionType $type): void
    {
        $enclosingFunction = $this->currentFunction;
        $this->currentFunction = $type;

        $this->beginScope();
        foreach ($stmt->params as $param) {
            $this->declare($param);
            $this->define($param);
        }

        $this->resolve($stmt->body);
        $this->endScope();

        $this->currentFunction = $enclosingFunction;
    }

    /**
     * @param Fi $stmt
     * @return void
     */
    public function visitFiStmt($stmt): void
    {
        $this->resolveExpr($stmt->condition);
        $this->resolveStmt($stmt->thenBranch);
        if ($stmt->elseBranch !== null) {
            $this->resolveStmt($stmt->elseBranch);
        }
    }

    /**
     * @param Prnt $stmt
     * @return void
     */
    public function visitPrntStmt($stmt)
    {
        $this->resolveExpr($stmt->expression);
    }

    /**
     * @param Rtrn $stmt
     * @return void
     */
    public function visitRtrnStmt($stmt): void
    {
        if ($stmt->value !== null) {
            if ($this->currentFunction === FunctionType::INITIALIZER) {
                $this->phlox->tokenTypeError($stmt->keyword, "Can't return a value from an initializer.");
            }

            $this->resolveExpr($stmt->value);
        }
    }

    /**
     * @param Vari $stmt
     * @return void
     */
    public function visitVariStmt($stmt): void
    {
        $this->declare($stmt->name);
        if ($stmt->initializer !== null) {
            $this->resolveExpr($stmt->initializer);
        }
        $this->define($stmt->name);
    }

    /**
     * @param Whle $stmt
     * @return void
     */
    public function visitWhleStmt($stmt): void
    {
        $this->resolveExpr($stmt->condition);
        $this->resolveStmt($stmt->body);
    }

    /**
     * @param Clas $stmt
     * @return void
     */
    public function visitClasStmt($stmt): void
    {
        $enclosingClass = $this->currentClass;
        $this->currentClass = ClassType::KLASS;

        $this->declare($stmt->name);
        $this->define($stmt->name);

        $this->beginScope();
        $this->scopes[count($this->scopes) - 1]["this"] = true;

        foreach ($stmt->methods as $method) {
            $declaration = FunctionType::METHOD;
            if ($method->name->lexeme === "init") {
                $declaration = FunctionType::INITIALIZER;
            }
            $this->resolveFunction($method, $declaration);
        }

        $this->endScope();

        $this->currentClass = $enclosingClass;
    }

    public function visitGetExpr(Get $expr): void
    {
        $this->resolveExpr($expr->object);
    }

    public function visitSetExpr(Set $expr): void
    {
        $this->resolveExpr($expr->value);
        $this->resolveExpr($expr->object);
    }

    public function visitThusExpr(Thus $expr): void
    {
        if ($this->currentClass === ClassType::NONE) {
            $this->phlox->tokenTypeError($expr->keyword, "Can't use 'this' outside of a class.");
            return;
        }

        $this->resolveLocal($expr, $expr->keyword);
    }
}