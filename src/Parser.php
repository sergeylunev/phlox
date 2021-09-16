<?php

namespace Phlox;

use Phlox\Expr\Binary;
use Phlox\Expr\Grouping;
use Phlox\Expr\Literal;
use Phlox\Expr\Unary;
use Phlox\Stmt\Expression;
use Phlox\Stmt\Prnt;
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
            $statements[] = $this->statement();
        }

        return $statements;
    }

    /**
     * @throws ParseError
     */
    private function expression(): Expr
    {
        return $this->equality();
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

        return $this->primary();
    }

    /**
     * @return Expr
     * @throws ParseError
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
     * @return Token
     * @throws ParseError
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
        if ($this->match(TokenType::TOKEN_PRINT)) {
            return $this->printStatement();
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
}