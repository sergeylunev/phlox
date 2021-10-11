<?php

namespace Phlox;

use Cassandra\Statement;
use http\Message\Body;
use Phlox\Expr\Assign;
use Phlox\Expr\Binary;
use Phlox\Expr\Call;
use Phlox\Expr\Grouping;
use Phlox\Expr\Literal;
use Phlox\Expr\Logical;
use Phlox\Expr\Unary;
use Phlox\Expr\Variable;
use Phlox\Stmt\Block;
use Phlox\Stmt\Expression;
use Phlox\Stmt\Fi;
use Phlox\Stmt\Prnt;
use Phlox\Stmt\Vari;
use Phlox\Stmt\Whle;
use Throwable;

class Parser
{
    /** @var Token[] */
    private array $tokens;

    private int $current = 0;

    private Phlox $phlox;

    public function __construct(Phlox $phlox, array $tokens)
    {
        $this->tokens = $tokens;
        $this->phlox = $phlox;
    }

    /**
     * @return Stmt[]
     */
    public function parse(): array
    {
        $statements = [];

        while (!$this->isAtEnd()) {
//            $statements[] = $this->statement();
            $statements[] = $this->declaration();
        }

        return $statements;
    }

    /**
     * @throws ParseError
     */
    private function expression(): Expr
    {
        return $this->assignment();
    }

    /**
     * @throws ParseError
     */
    private function assignment(): Expr
    {
        $expr = $this->or();

        if ($this->match(TokenType::TOKEN_EQUAL)) {
            $equals = $this->previous();
            $value = $this->assignment();

            if ($expr instanceof Variable) {
                $name = $expr->name;

                return new Assign($name, $value);
            }

            $this->error($equals, 'Invalid assignment target.');
        }

        return $expr;
    }

    /**
     * @throws ParseError
     */
    private function equality(): Expr
    {
        $expr = $this->comparison();

        while ($this->match(TokenType::TOKEN_BANG_EQUAL, TokenType::TOKEN_EQUAL_EQUAL)) {
            $operator = $this->previous();
            $right = $this->comparison();
            $expr = new Binary($expr, $operator, $right);
        }

        return $expr;
    }

    /**
     * @throws ParseError
     */
    private function comparison(): Expr
    {
        $expr = $this->term();

        while ($this->match(
            TokenType::TOKEN_GREATER,
            TokenType::TOKEN_GREATER_EQUAL,
            TokenType::TOKEN_LESS,
            TokenType::TOKEN_GREATER_EQUAL
        )) {
            $operator = $this->previous();
            $right = $this->term();

            $expr = new Binary($expr, $operator, $right);
        }

        return $expr;
    }

    private function match(...$types): bool
    {
        foreach ($types as $type) {
            if ($this->check($type)) {
                $this->advance();

                return true;
            }
        }

        return false;
    }

    private function check(mixed $type): bool
    {
        if ($this->isAtEnd()) {
            return false;
        }

        return $this->peek()->tokenType === $type;
    }

    private function advance(): Token
    {
        if (!$this->isAtEnd()) {
            $this->current++;
        }

        return $this->previous();
    }

    private function isAtEnd(): bool
    {
        return $this->peek()->tokenType === TokenType::TOKEN_EOF;
    }

    private function peek(): Token
    {
        return $this->tokens[$this->current];
    }

    private function previous()
    {
        return $this->tokens[$this->current - 1];
    }

    /**
     * @throws ParseError
     */
    private function term(): Expr
    {
        $expr = $this->factor();

        while ($this->match(
            TokenType::TOKEN_MINUS,
            TokenType::TOKEN_PLUS
        )) {
            $operator = $this->previous();
            $right = $this->factor();

            $expr = new Binary($expr, $operator, $right);
        }

        return $expr;
    }

    /**
     * @throws ParseError
     */
    private function factor(): Expr
    {
        $expr = $this->unary();

        while ($this->match(
            TokenType::TOKEN_SLASH,
            TokenType::TOKEN_STAR
        )) {
            $operator = $this->previous();
            $right = $this->unary();

            $expr = new Binary($expr, $operator, $right);
        }

        return $expr;
    }

    /**
     * @throws ParseError
     */
    private function unary(): Expr
    {
        if ($this->match(TokenType::TOKEN_BANG, TokenType::TOKEN_MINUS)) {
            $operator = $this->previous();
            $right = $this->unary();

            return new Unary($operator, $right);
        }

        return $this->call();
    }

    /**
     * @throws ParseError
     * @return Expr
     */
    private function primary(): Expr
    {
        if ($this->match(TokenType::TOKEN_FALSE)) {
            return new Literal(false);
        }
        if ($this->match(TokenType::TOKEN_TRUE)) {
            return new Literal(true);
        }
        if ($this->match(TokenType::TOKEN_NIL)) {
            return new Literal(null);
        }

        if ($this->match(TokenType::TOKEN_NUMBER, TokenType::TOKEN_STRING)) {
            return new Literal($this->previous()->literal);
        }

        if ($this->match(TokenType::TOKEN_IDENTIFIER)) {
            return new Variable($this->previous());
        }

        if ($this->match(TokenType::TOKEN_LEFT_PAREN)) {
            $expr = $this->expression();
            $this->consume(TokenType::TOKEN_RIGHT_PAREN, "Expect ')' after expression.");

            return new Grouping($expr);
        }

        throw $this->error($this->peek(), "Expect expression");
    }

    /**
     * @param string $tokenType
     * @param string $message
     *
     * @throws ParseError
     * @return Token
     */
    private function consume(string $tokenType, string $message): Token
    {
        if ($this->check($tokenType)) {
            return $this->advance();
        }

        throw $this->error($this->peek(), $message);
    }

    private function error(Token $token, string $message): ParseError
    {
        $this->phlox->tokenTypeError($token, $message);

        return new ParseError();
    }

    private function synchronize(): void
    {
        $this->advance();

        while (!$this->isAtEnd()) {
            if ($this->previous()->tokenType === TokenType::TOKEN_SEMICOLON) {
                return;
            }

            switch ($this->peek()->tokenType) {
                case TokenType::TOKEN_CLASS:
                case TokenType::TOKEN_FUN:
                case TokenType::TOKEN_VAR:
                case TokenType::TOKEN_FOR:
                case TokenType::TOKEN_IF:
                case TokenType::TOKEN_WHILE:
                case TokenType::TOKEN_PRINT:
                case TokenType::TOKEN_RETURN:
                    return;
            }

            $this->advance();
        }
    }

    /**
     * @throws ParseError
     */
    private function statement(): Stmt
    {
        if ($this->match(TokenType::TOKEN_FOR)) {
            return $this->forStatement();
        }
        if ($this->match(TokenType::TOKEN_IF)) {
            return $this->ifStatement();
        }
        if ($this->match(TokenType::TOKEN_PRINT)) {
            return $this->printStatement();
        }
        if ($this->match(TokenType::TOKEN_WHILE)) {
            return $this->whileStatement();
        }
        if ($this->match(TokenType::TOKEN_LEFT_BRACE)) {
            return new Block($this->block());
        }

        return $this->expressionStatement();
    }

    /**
     * @throws ParseError
     */
    private function printStatement(): Stmt
    {
        $value = $this->expression();
        $this->consume(TokenType::TOKEN_SEMICOLON, "Expect ';' after value.");

        return new Prnt($value);
    }

    /**
     * @throws ParseError
     */
    private function expressionStatement(): Stmt
    {
        $value = $this->expression();
        $this->consume(TokenType::TOKEN_SEMICOLON, "Expect ';' after value.");

        return new Expression($value);
    }

    private function declaration(): ?Stmt
    {
        try {
            if ($this->match(TokenType::TOKEN_VAR)) {
                return $this->varDeclaration();
            }

            return $this->statement();
        } catch (ParseError $exception) {
            $this->synchronize();

            return null;
        }
    }

    /**
     * @throws ParseError
     */
    private function varDeclaration(): Stmt
    {
        $name = $this->consume(TokenType::TOKEN_IDENTIFIER, 'Expect variable name.');

        $initializer = null;

        if ($this->match(TokenType::TOKEN_EQUAL)) {
            $initializer = $this->expression();
        }

        $this->consume(TokenType::TOKEN_SEMICOLON, "Expect ';' after variable declaration.");

        return new Vari($name, $initializer);
    }

    private function block(): array
    {
        $statements = [];

        while (!$this->check(TokenType::TOKEN_RIGHT_BRACE) && !$this->isAtEnd()) {
            $statements[] = $this->declaration();
        }

        $this->consume(TokenType::TOKEN_RIGHT_BRACE, "Expect '}' after block.");

        return $statements;
    }

    private function ifStatement(): Stmt
    {
        $this->consume(TokenType::TOKEN_LEFT_PAREN, "Expect '(' after 'if'.");
        $condition = $this->expression();
        $this->consume(TokenType::TOKEN_RIGHT_PAREN, "Expect ')' after if condition.");

        $thenBranch = $this->statement();
        $elseBranch = null;

        if ($this->match(TokenType::TOKEN_ELSE)) {
            $elseBranch = $this->statement();
        }

        return new Fi($condition, $thenBranch, $elseBranch);
    }

    private function or(): Expr
    {
        $expr = $this->and();

        while ($this->match(TokenType::TOKEN_OR)) {
            $operator = $this->previous();
            $right = $this->and();

            $expr = new Logical($expr, $operator, $right);
        }

        return $expr;
    }

    private function and(): Expr
    {
        $expr = $this->equality();

        while ($this->match(TokenType::TOKEN_AND)) {
            $operator = $this->previous();
            $right = $this->equality();

            $expr = new Logical($expr, $operator, $right);
        }

        return $expr;
    }

    /**
     * @throws ParseError
     */
    private function whileStatement(): Stmt
    {
        $this->consume(TokenType::TOKEN_LEFT_PAREN, "Expect '(' after 'while'.");
        $condition = $this->expression();
        $this->consume(TokenType::TOKEN_RIGHT_PAREN, "Expect ')' after condition.");
        $body = $this->statement();

        return new Whle($condition, $body);
    }

    /**
     * @throws ParseError
     */
    private function forStatement(): Stmt
    {
        $this->consume(TokenType::TOKEN_LEFT_PAREN, "Expect '(' after 'for'.");

        $initializer = null;
        if ($this->match(TokenType::TOKEN_SEMICOLON)) {
            $initializer = null;
        } elseif ($this->match(TokenType::TOKEN_VAR)) {
            $initializer = $this->varDeclaration();
        } else {
            $initializer = $this->expressionStatement();
        }

        $condition = null;
        if (!$this->check(TokenType::TOKEN_SEMICOLON)) {
            $condition = $this->expression();
        }
        $this->consume(TokenType::TOKEN_SEMICOLON, "Expect ';' after loop condition.");

        $incriment = null;
        if (!$this->check(TokenType::TOKEN_RIGHT_PAREN)) {
            $incriment = $this->expression();
        }
        $this->consume(TokenType::TOKEN_RIGHT_PAREN, "Expect ')' after for clauses.");

        $body = $this->statement();

        if ($incriment !== null) {
            $body = new Block([
                $body,
                new Expression($incriment)
            ]);
        }

        if ($condition === null) {
            $condition = new Literal(true);
        }
        $body = new Whle($condition, $body);

        if ($initializer !== null) {
            $body = new Block([$initializer, $body]);
        }

        return $body;
    }

    /**
     * @throws ParseError
     */
    private function call(): Expr
    {
        $expr = $this->primary();

        while (true) {
            if ($this->match(TokenType::TOKEN_LEFT_PAREN)) {
                $expr = $this->finishCall($expr);
            } else {
                break;
            }
        }

        return $expr;
    }

    /**
     * @throws ParseError
     */
    private function finishCall(mixed $callee): Expr
    {
        $arguments = [];
        if (!$this->check(TokenType::TOKEN_RIGHT_PAREN)) {
            do {
                if (count($arguments) >= 255) {
                    $this->error($this->peek(), "Can't have more than 255 arguments.");
                }
                $arguments[] = $this->expression();
            } while ($this->match(TokenType::TOKEN_COMMA));
        }

        $paren = $this->consume(TokenType::TOKEN_RIGHT_PAREN, "Expect ')' after arguments.");

        return new Call($callee, $paren, $arguments);
    }
}