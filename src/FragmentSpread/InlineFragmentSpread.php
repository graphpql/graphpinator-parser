<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\FragmentSpread;

use Graphpinator\Parser\Directive\DirectiveSet;
use Graphpinator\Parser\Field\FieldSet;
use Graphpinator\Parser\TypeRef\NamedTypeRef;

final readonly class InlineFragmentSpread implements FragmentSpread
{
    public function __construct(
        public FieldSet $fields,
        public DirectiveSet $directives = new DirectiveSet(),
        public ?NamedTypeRef $typeCond = null,
    )
    {
    }
}
