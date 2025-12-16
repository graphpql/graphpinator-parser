<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Value;

final class VariableRef implements Value
{
    public function __construct(
        private string $varName,
    )
    {
    }

    #[\Override]
    public function getRawValue() : never
    {
        throw new \RuntimeException('Operation not supported.');
    }

    public function getVarName() : string
    {
        return $this->varName;
    }

    #[\Override]
    public function accept(ValueVisitor $valueVisitor) : mixed
    {
        return $valueVisitor->visitVariableRef($this);
    }
}
