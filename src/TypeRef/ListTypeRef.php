<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\TypeRef;

final readonly class ListTypeRef implements TypeRef
{
    public function __construct(
        public TypeRef $innerRef,
    )
    {
    }

    #[\Override]
    public function print() : string
    {
        return '[' . $this->innerRef->print() . ']';
    }
}
