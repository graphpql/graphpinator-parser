<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Value;

interface Value
{
    /**
     * @phpcs:ignore
     * @return \stdClass|list<mixed>|string|int|float|bool|null
     */
    public function getRawValue() : \stdClass|array|string|int|float|bool|null;

    public function accept(ValueVisitor $valueVisitor) : mixed; // @phpcs:ignore
}
