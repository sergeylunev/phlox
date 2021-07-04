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

        foreach ($tokens as $token) {
            $this->output->writeln($token);
        }
    }

    public function error(int $line, string $message): void
    {
        $this->report($line, "", $message);
    }

    private function report(int $line, string $where, string $message): void
    {
        $this->output->writeln(
            sprintf("[line %d] Error %s: %s", $line, $where, $message)
        );

        $this->hadError = true;
    }
}