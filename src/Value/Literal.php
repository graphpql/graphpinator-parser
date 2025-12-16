<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Value;

final class Literal implements Value
{
    public function __construct(
        private string|int|float|bool|null $value,
    )
    {
    }

    #[\Override]
    public function getRawValue() : string|int|float|bool|null
    {
        return $this->value;
    }

    #[\Override]
    public function accept(ValueVisitor $valueVisitor) : mixed
    {
        return $valueVisitor->visitLiteral($this);
    }
}
