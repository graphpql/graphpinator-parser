<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Operation;

use Graphpinator\Parser\Directive\DirectiveSet;
use Graphpinator\Parser\Field\FieldSet;
use Graphpinator\Parser\OperationType;
use Graphpinator\Parser\Variable\VariableSet;

final readonly class Operation
{
    public function __construct(
        public OperationType $type,
        public FieldSet $children,
        public ?string $name = null,
        public VariableSet $variables = new VariableSet(),
        public DirectiveSet $directives = new DirectiveSet(),
    )
    {
    }
}
