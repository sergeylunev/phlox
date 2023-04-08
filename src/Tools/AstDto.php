<?php

declare(strict_types=1);

namespace Phlox\Tools;

class AstDto
{
    public function __construct(
        readonly public string $name,
        readonly public array $imports,
        readonly public array $types
    )
    {
    }
}