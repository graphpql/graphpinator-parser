<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Field;

use Graphpinator\Parser\Directive\DirectiveSet;
use Graphpinator\Parser\Value\ArgumentValueSet;

final readonly class Field
{
    public function __construct(
        public string $name,
        public ?string $alias = null,
        public ?FieldSet $children = null,
        public ?ArgumentValueSet $arguments = null,
        public ?DirectiveSet $directives = null,
    )
    {
    }
}
