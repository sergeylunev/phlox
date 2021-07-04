<?php

namespace Phlox;

class Token
{
    private string $tokenType;
    private string $lexeme;
    private $literal;
    private int $line;

    public function __construct(
        string $tokenType,
        string $lexeme,
        $literal,
        int $line
    ) {
        $this->tokenType = $tokenType;
        $this->lexeme = $lexeme;
        $this->literal = $literal;
        $this->line = $line;
    }

    public function __toString(): string
    {
        return sprintf(
            "%s %s %s",
            $this->tokenType,
            $this->lexeme,
            $this->literal
        );
    }
}
