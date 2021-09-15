<?php


namespace Phlox;


use Symfony\Component\Console\Output\OutputInterface;

class Phlox
{

    private OutputInterface $output;
    private bool $hadError = false;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function main()
    {

    }

    public function runFile(string $path)
    {
        if ($this->hadError) {
            throw new \Exception();
        }
    }

    public function runString(string $string)
    {
        $this->run($string);
    }

    private function run(string $source): void
    {
        $scanner = new Scanner($source, $this);
        $tokens = $scanner->scanTokens();

        $parser = new Parser($this, $tokens);
        $expression = $parser->parse();

        if ($this->hadError) {
            return;
        }

        $this->output->writeln((new AstPrinter())->print($expression));
    }

    public function error(int $line, string $message): void
    {
        $this->report($line, "", $message);
    }

public function tokenTypeError(Token $token, string $message): void
    {
        if ($token->tokenType === TokenType::TOKEN_EOF) {
            $this->report($token->line, " at end", $message);
        } else {
            $this->report($token->line, " at '" . $token->lexeme . "'", $message);
        }

    }

    private function report(int $line, string $where, string $message): void
    {
        $this->output->writeln(
            sprintf("[line %d] Error %s: %s", $line, $where, $message)
        );

        $this->hadError = true;
    }
}