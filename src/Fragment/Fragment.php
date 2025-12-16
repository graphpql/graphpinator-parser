<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Fragment;

use Graphpinator\Parser\Directive\DirectiveSet;
use Graphpinator\Parser\Field\FieldSet;
use Graphpinator\Parser\TypeRef\NamedTypeRef;

final readonly class Fragment
{
    public function __construct(
        public string $name,
        public NamedTypeRef $typeCond,
        public DirectiveSet $directives,
        public FieldSet $fields,
    )
    {
    }
}
