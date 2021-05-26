<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\FragmentSpread;

final class InlineFragmentSpread implements \Graphpinator\Parser\FragmentSpread\FragmentSpread
{
    use \Nette\SmartObject;

    private \Graphpinator\Parser\Directive\DirectiveSet $directives;

    public function __construct(
        private \Graphpinator\Parser\Field\FieldSet $fields,
        ?\Graphpinator\Parser\Directive\DirectiveSet $directives = null,
        private ?\Graphpinator\Parser\TypeRef\NamedTypeRef $typeCond = null,
    )
    {
        $this->directives = $directives
            ?? new \Graphpinator\Parser\Directive\DirectiveSet();
    }

    public function getFields() : \Graphpinator\Parser\Field\FieldSet
    {
        return $this->fields;
    }

    public function getDirectives() : \Graphpinator\Parser\Directive\DirectiveSet
    {
        return $this->directives;
    }

    public function getTypeCond() : ?\Graphpinator\Parser\TypeRef\NamedTypeRef
    {
        return $this->typeCond;
    }
}
