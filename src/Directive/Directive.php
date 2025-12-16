<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Directive;

use Graphpinator\Parser\Value\ArgumentValueSet;

final readonly class Directive
{
    public function __construct(
        public string $name,
        public ?ArgumentValueSet $arguments,
    )
    {
    }
}
