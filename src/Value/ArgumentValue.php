<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Value;

final readonly class ArgumentValue
{
    public function __construct(
        public Value $value,
        public string $name,
    )
    {
    }
}
