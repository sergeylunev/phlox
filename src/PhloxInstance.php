<?php

declare(strict_types=1);

namespace Phlox;

class PhloxInstance
{
    public function __construct(readonly private PhloxClass $klass)
    {
    }

    public function __toString(): string
    {
        return $this->klass->name . " instance";
    }
}