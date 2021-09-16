<?php

namespace Phlox;

class Scanner
{
    private string $source;
    private array $tokens;

    private int $start = 0;
    private int $current = 0;
    private int $line = 1;

    private array $keywords = [
        'and' => TokenType::TOKEN_AND,
        "class" => TokenType::TOKEN_CLASS,
        "else" => TokenType::TOKEN_ELSE,
        "false" => TokenType::TOKEN_FALSE,
        "for" => TokenType::TOKEN_FOR,
        "fun" => TokenType::TOKEN_FUN,
        "if" => TokenType::TOKEN_IF,
        "nil" => TokenType::TOKEN_NIL,
        "or" => TokenType::TOKEN_OR,
        "print" => TokenType::TOKEN_PRINT,
        "return" => TokenType::TOKEN_RETURN,
        "super" => TokenType::TOKEN_SUPER,
        "this" => TokenType::TOKEN_THIS,
        "true" => TokenType::TOKEN_TRUE,
        "var" => TokenType::TOKEN_VAR,
        "while" => TokenType::TOKEN_WHILE,
    ];

    private Phlox $phlox;
    private int $sourceLength;

    public function __construct(
        string $source,
        Phlox $phlox
    )
    {
        $this->source = $source;

        $this->sourceLength = mb_strlen($this->source);

        $this->phlox = $phlox;
    }

    public function scanTokens(): array
    {
        while (!$this->isAtEnd()) {
            $this->start = $this->current;
            $this->scanToken();
        }

        $token = new Token(TokenType::TOKEN_EOF, "", null, $this->line);
        $this->tokens[] = $token;

        return $this->tokens;
    }

    private function scanToken()
    {
        $c = $this->advance();

        switch ($c) {
            case '(':
                $this->addToken(TokenType::TOKEN_LEFT_PAREN);
                break;
            case ')':
                $this->addToken(TokenType::TOKEN_RIGHT_PAREN);
                break;
            case '{':
                $this->addToken(TokenType::TOKEN_LEFT_BRACE);
                break;
            case '}':
                $this->addToken(TokenType::TOKEN_RIGHT_BRACE);
                break;
            case ',':
                $this->addToken(TokenType::TOKEN_COMMA);
                break;
            case '.':
                $this->addToken(TokenType::TOKEN_DOT);
                break;
            case '-':
                $this->addToken(TokenType::TOKEN_MINUS);
                break;
            case '+':
                $this->addToken(TokenType::TOKEN_PLUS);
                break;
            case ';':
                $this->addToken(TokenType::TOKEN_SEMICOLON);
                break;
            case '*':
                $this->addToken(TokenType::TOKEN_STAR);
                break;
            case '!':
                $this->addToken($this->match('=')
                    ? TokenType::TOKEN_BANG_EQUAL
                    : TokenType::TOKEN_BANG
                );
                break;
            case '=':
                $this->addToken($this->match('=')
                    ? TokenType::TOKEN_EQUAL_EQUAL
                    : TokenType::TOKEN_EQUAL
                );
                break;
            case '<':
                $this->addToken($this->match('=')
                    ? TokenType::TOKEN_LESS_EQUAL
                    : TokenType::TOKEN_LESS
                );
                break;
            case '>':
                $this->addToken($this->match('=')
                    ? TokenType::TOKEN_GREATER_EQUAL
                    : TokenType::TOKEN_GREATER
                );
                break;
            case '/':
                if ($this->match("/")) {
                    while ($this->peek() != "\n" && !$this->isAtEnd()) {
                        $this->advance();
                    }
                } else {
                    $this->addToken(TokenType::TOKEN_SLASH);
                }
                break;
            case ' ':
            case "\r":
            case "\t":
                break;
            case "\n":
                $this->line++;
                break;
            case '"':
                $this->string();
                break;
            default:
                if ($this->isDigit($c)) {
                    $this->number();
                } else if ($this->isAlpha($c)) {
                    $this->identifier();
                } else {
                    $this->phlox->error($this->line, "Unexpected character.");
                }
                break;
        }
    }

    private function identifier()
    {
        while ($this->isAlphaNumeric($this->peek())) {
            $this->advance();
        }

        $text = mb_substr($this->source, $this->start, $this->current - $this->sourceLength);
        $isKeyword = key_exists($text, $this->keywords);
        $type = TokenType::TOKEN_IDENTIFIER;
        if ($isKeyword) {
            $type = $this->keywords[$text];
        }

        $this->addToken($type);
    }

    private function isAlpha(string $c): bool
    {
        return ctype_alpha($c) || $c === '_';
    }

    private function isAlphaNumeric(string $c): bool
    {
        return $this->isAlpha($c) || $this->isDigit($c);
    }

    private function isAtEnd(): bool
    {
        return $this->current >= mb_strlen($this->source);
    }

    private function advance(): string
    {
        return $this->_charAt($this->current++);
    }

    private function addToken(string $type, $literal = null): void
    {
        $text = mb_substr($this->source, $this->start, $this->current - $this->start);
        $this->tokens[] = new Token($type, $text, $literal, $this->line);
    }

    private function _charAt(int $position): string
    {
        return mb_substr($this->source, $position, 1);
    }

    private function match(string $expected): bool
    {
        if ($this->isAtEnd()) {
            return false;
        }

        if ($this->_charAt($this->current) !== $expected) {
            return false;
        }

        $this->current++;
        return true;
    }

    private function peek(): string
    {
        if ($this->isAtEnd()) {
            return "\0";
        }

        $result = $this->_charAt($this->current);

        return $result;
    }

    private function string(): void
    {
        while ($this->peek() != '"' && !$this->isAtEnd()) {
            if ($this->peek() === "\n") {
                $this->line++;
            }
            $this->advance();
        }

        if ($this->isAtEnd()) {
            $this->phlox->error($this->line, "Unterminated string.");
        }

        $this->advance();
        $value = mb_substr($this->source, $this->start + 1, $this->current - ($this->sourceLength + 1));
        $this->addToken(TokenType::TOKEN_STRING, $value);
    }

    private function isDigit(string $char): bool
    {
        return $char >= '0' && $char <= '9';
    }

    private function number(): void
    {
        while ($this->isDigit($this->peek())) {
            $this->advance();
        }

        if ($this->peek() === '.' && $this->isDigit($this->peekNext())) {
            $this->advance();
            while ($this->isDigit($this->peek())) {
                $this->advance();
            }
        }

        $this->addToken(
            TokenType::TOKEN_NUMBER,
            (double)$this->_getSourcePart($this->start, $this->current)
        );
    }

    private function peekNext(): string
    {
        if ($this->current + 1 >= mb_strlen($this->source)) {
            return "\0";
        }

        return $this->_charAt($this->current + 1);

    }

    public function _getSourcePart(int $from, int $to): string
    {
        return mb_substr($this->source, $from, $to);
    }
}

