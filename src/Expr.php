<?php

declare(strict_types=1);

namespace Phlox;


abstract class Expr
{
    public abstract function accept(ExprVisitor $visitor);
}
