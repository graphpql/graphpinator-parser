<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Operation;

final class Operation
{
    use \Nette\SmartObject;

    private ?\Graphpinator\Parser\Variable\VariableSet $variables;
    private ?\Graphpinator\Parser\Directive\DirectiveSet $directives;

    public function __construct(
        private string $type,
        private ?string $name,
        ?\Graphpinator\Parser\Variable\VariableSet $variables,
        ?\Graphpinator\Parser\Directive\DirectiveSet $directives,
        private \Graphpinator\Parser\Field\FieldSet $children,
    )
    {
        $this->variables = $variables
            ?? new \Graphpinator\Parser\Variable\VariableSet();
        $this->directives = $directives
            ?? new \Graphpinator\Parser\Directive\DirectiveSet();
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getName() : ?string
    {
        return $this->name;
    }

    public function getFields() : \Graphpinator\Parser\Field\FieldSet
    {
        return $this->children;
    }

    public function getVariables() : \Graphpinator\Parser\Variable\VariableSet
    {
        return $this->variables;
    }

    public function getDirectives() : \Graphpinator\Parser\Directive\DirectiveSet
    {
        return $this->directives;
    }
}
