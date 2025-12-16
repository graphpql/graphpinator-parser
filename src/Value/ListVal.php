<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Value;

final class ListVal implements Value
{
    public function __construct(
        /** @var list<Value> */
        private array $value,
    )
    {
    }

    /**
     * @return list<Value>
     */
    public function getValue() : array
    {
        return $this->value;
    }

    /**
     * @phpcs:ignore
     * @return list<mixed>
     */
    #[\Override]
    public function getRawValue() : array
    {
        $return = [];

        foreach ($this->value as $value) {
            $return[] = $value->getRawValue();
        }

        return $return;
    }

    #[\Override]
    public function accept(ValueVisitor $valueVisitor) : mixed
    {
        return $valueVisitor->visitListVal($this);
    }
}
