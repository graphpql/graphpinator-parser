<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Variable;

use Graphpinator\Parser\Directive\DirectiveSet;
use Graphpinator\Parser\TypeRef\TypeRef;
use Graphpinator\Parser\Value\Value;

final readonly class Variable
{
    public function __construct(
        public string $name,
        public TypeRef $type,
        public ?Value $default,
        public DirectiveSet $directives,
    )
    {
    }
}
