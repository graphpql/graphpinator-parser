<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Variable;

use Graphpinator\Parser\Directive\DirectiveSet;
use Graphpinator\Parser\TypeRef\TypeRef;
use Graphpinator\Parser\Value\Value;

final class Variable
{
    public function __construct(
        private string $name,
        private TypeRef $type,
        private ?Value $default,
        private DirectiveSet $directives,
    )
    {
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getType() : TypeRef
    {
        return $this->type;
    }

    public function getDefault() : ?Value
    {
        return $this->default;
    }

    public function getDirectives() : DirectiveSet
    {
        return $this->directives;
    }
}
