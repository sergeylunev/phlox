<?php

declare(strict_types=1);

namespace Phlox;

abstract class Stmt
{
    public abstract function accept(Visitor $visitor);
}
