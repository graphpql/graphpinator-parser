<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Operation;

use Graphpinator\Parser\Directive\DirectiveSet;
use Graphpinator\Parser\Field\FieldSet;
use Graphpinator\Parser\Variable\VariableSet;

final class Operation
{
    private ?VariableSet $variables;
    private ?DirectiveSet $directives;

    public function __construct(
        private string $type,
        private ?string $name,
        ?VariableSet $variables,
        ?DirectiveSet $directives,
        private FieldSet $children,
    )
    {
        $this->variables = $variables
            ?? new VariableSet();
        $this->directives = $directives
            ?? new DirectiveSet();
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function getFields() : FieldSet
    {
        return $this->children;
    }

    public function getVariables() : VariableSet
    {
        return $this->variables;
    }

    public function getDirectives() : DirectiveSet
    {
        return $this->directives;
    }
}
