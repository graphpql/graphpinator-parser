<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Field;

use Graphpinator\Parser\Directive\DirectiveSet;
use Graphpinator\Parser\Value\ArgumentValueSet;

final class Field
{
    public function __construct(
        private string $name,
        private ?string $alias = null,
        private ?FieldSet $children = null,
        private ?ArgumentValueSet $arguments = null,
        private ?DirectiveSet $directives = null,
    )
    {
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getAlias() : ?string
    {
        return $this->alias;
    }

    public function getFields() : ?FieldSet
    {
        return $this->children;
    }

    public function getArguments() : ?ArgumentValueSet
    {
        return $this->arguments;
    }

    public function getDirectives() : ?DirectiveSet
    {
        return $this->directives;
    }
}
