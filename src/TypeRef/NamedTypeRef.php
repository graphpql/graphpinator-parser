<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\TypeRef;

final readonly class NamedTypeRef implements TypeRef
{
    public function __construct(
        public string $name,
    )
    {
    }

    #[\Override]
    public function print() : string
    {
        return $this->name;
    }
}
