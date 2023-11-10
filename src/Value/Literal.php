<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Value;

final class Literal implements \Graphpinator\Parser\Value\Value
{
    public function __construct(
        private string|int|float|bool|null $value,
    )
    {
    }

    public function getRawValue() : string|int|float|bool|null
    {
        return $this->value;
    }

    public function accept(ValueVisitor $valueVisitor) : mixed
    {
        return $valueVisitor->visitLiteral($this);
    }
}
