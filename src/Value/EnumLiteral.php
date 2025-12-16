<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Value;

final class EnumLiteral implements Value
{
    public function __construct(
        private string $value,
    )
    {
    }

    #[\Override]
    public function getRawValue() : string
    {
        return $this->value;
    }

    #[\Override]
    public function accept(ValueVisitor $valueVisitor) : mixed
    {
        return $valueVisitor->visitEnumLiteral($this);
    }
}
