<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Value;

final readonly class EnumLiteral implements Value
{
    public function __construct(
        public string $value,
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
