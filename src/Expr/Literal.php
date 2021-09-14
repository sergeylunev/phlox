<?php

declare(strict_types=1);

namespace Phlox\Expr;

use Phlox\Expr;
use Phlox\Visitor;

class Literal extends Expr
{
    public mixed $value;

    public function __construct(mixed $value)
    {
        $this->value = $value;
    }
    public function accept(Visitor $visitor)
    {
        return $visitor->visitLiteralExpr($this);
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
