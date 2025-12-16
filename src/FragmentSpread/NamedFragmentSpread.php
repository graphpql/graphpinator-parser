<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\FragmentSpread;

use Graphpinator\Parser\Directive\DirectiveSet;

final class NamedFragmentSpread implements FragmentSpread
{
    private DirectiveSet $directives;

    public function __construct(
        private string $name,
        ?DirectiveSet $directives = null,
    )
    {
        $this->directives = $directives
            ?? new DirectiveSet();
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getDirectives() : DirectiveSet
    {
        return $this->directives;
    }
}
