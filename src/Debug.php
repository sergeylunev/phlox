<?php

namespace Phlox;

use Phlox\Expr\Binary;
use Phlox\Expr\Grouping;
use Phlox\Expr\Literal;
use Phlox\Expr\Unary;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Debug extends Command
{
    protected static $defaultName = 'app:debug';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $expression = new Binary(
            new Unary(
                new Token(TokenType::TOKEN_MINUS, '-', null, 1),
                new Literal(123)
            ),
            new Token(TokenType::TOKEN_STAR, '*', null, 1),
            new Grouping(
                new Literal(45.67)
            )
        );

        $output->writeln((new AstPrinter())->print($expression));

        return Command::SUCCESS;
    }
}