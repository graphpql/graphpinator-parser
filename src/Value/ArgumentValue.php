<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Value;

final class ArgumentValue
{
    public function __construct(
        private Value $value,
        private string $name,
    )
    {
    }

    public function getValue() : Value
    {
        return $this->value;
    }

    public function getName() : string
    {
        return $this->name;
    }
}
