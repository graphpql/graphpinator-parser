<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Directive;

use Graphpinator\Parser\Value\ArgumentValueSet;

final class Directive
{
    public function __construct(
        private string $name,
        private ?ArgumentValueSet $arguments,
    )
    {
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getArguments() : ?ArgumentValueSet
    {
        return $this->arguments;
    }
}
