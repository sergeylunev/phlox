<?php


namespace Phlox;


use Symfony\Component\Console\Output\OutputInterface;

class Phlox
{
    private OutputInterface $output;
    private bool $hadError = false;
    private bool $hadRuntimeError = false;
    private Interpreter $interpreter;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->interpreter = new Interpreter($output, $this);
    }

    public function main()
    {

    }

    public function runFile(string $path): void
    {
        if (!file_exists($path)) {
            throw new \Exception("There is no file on {$path}");
        }

        $string = file_get_contents($path);
        $this->run($string);

        if ($this->hadError || $this->hadRuntimeError) {
            throw new \Exception();
        }
    }

    public function runString(string $string): void
    {
        $this->run($string);
    }

    private function run(string $source): void
    {
        $scanner = new Scanner($source, $this);
        $tokens = $scanner->scanTokens();

        $parser = new Parser($this, $tokens);
        $statements = $parser->parse();

        if ($this->hadError) {
            return;
        }

        $resolver = new Resolver($this, $this->interpreter);
        $resolver->resolve($statements);

        if ($this->hadError) {
            return;
        }

        $this->interpreter->interpret($statements);
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

    public function runtimeError(RuntimeError $exception)
    {
        $this->output->writeln($exception->getMessage() . "\n[line " . $exception->token->line . ']');
        $this->hadRuntimeError = true;
    }
}