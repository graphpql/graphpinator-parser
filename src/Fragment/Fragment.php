<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Fragment;

use Graphpinator\Parser\Directive\DirectiveSet;
use Graphpinator\Parser\Field\FieldSet;
use Graphpinator\Parser\TypeRef\NamedTypeRef;

final class Fragment
{
    public function __construct(
        private string $name,
        private NamedTypeRef $typeCond,
        private DirectiveSet $directives,
        private FieldSet $fields,
    )
    {
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getFields() : FieldSet
    {
        return $this->fields;
    }

    public function getTypeCond() : NamedTypeRef
    {
        return $this->typeCond;
    }

    public function getDirectives() : DirectiveSet
    {
        return $this->directives;
    }
}
