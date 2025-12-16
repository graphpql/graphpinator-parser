<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Value;

final readonly class ObjectVal implements Value
{
    public function __construct(
        public \stdClass $value,
    )
    {
    }

    #[\Override]
    public function getRawValue() : \stdClass
    {
        $return = new \stdClass();

        foreach ((array) $this->value as $key => $value) {
            \assert($value instanceof Value);

            $return->{$key} = $value->getRawValue();
        }

        return $return;
    }

    #[\Override]
    public function accept(ValueVisitor $valueVisitor) : mixed
    {
        return $valueVisitor->visitObjectVal($this);
    }
}
