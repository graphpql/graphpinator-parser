<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\TypeRef;

final class ListTypeRef implements TypeRef
{
    public function __construct(
        private TypeRef $innerRef,
    )
    {
    }

    public function getInnerRef() : TypeRef
    {
        return $this->innerRef;
    }

    #[\Override]
    public function print() : string
    {
        return '[' . $this->innerRef->print() . ']';
    }
}
