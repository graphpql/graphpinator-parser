<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Value;

final readonly class VariableRef implements Value
{
    public function __construct(
        public string $varName,
    )
    {
    }

    #[\Override]
    public function getRawValue() : never
    {
        throw new \RuntimeException('Operation not supported.');
    }

    #[\Override]
    public function accept(ValueVisitor $valueVisitor) : mixed
    {
        return $valueVisitor->visitVariableRef($this);
    }
}
