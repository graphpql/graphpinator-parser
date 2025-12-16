<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\FragmentSpread;

use Graphpinator\Parser\Directive\DirectiveSet;
use Graphpinator\Parser\Field\FieldSet;
use Graphpinator\Parser\TypeRef\NamedTypeRef;

final class InlineFragmentSpread implements FragmentSpread
{
    private DirectiveSet $directives;

    public function __construct(
        private FieldSet $fields,
        ?DirectiveSet $directives = null,
        private ?NamedTypeRef $typeCond = null,
    )
    {
        $this->directives = $directives
            ?? new DirectiveSet();
    }

    public function getFields() : FieldSet
    {
        return $this->fields;
    }

    public function getDirectives() : DirectiveSet
    {
        return $this->directives;
    }

    public function getTypeCond() : ?NamedTypeRef
    {
        return $this->typeCond;
    }
}
