<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\FragmentSpread;

final class NamedFragmentSpread implements \Graphpinator\Parser\FragmentSpread\FragmentSpread
{
    private \Graphpinator\Parser\Directive\DirectiveSet $directives;

    public function __construct(
        private string $name,
        ?\Graphpinator\Parser\Directive\DirectiveSet $directives = null,
    )
    {
        $this->directives = $directives
            ?? new \Graphpinator\Parser\Directive\DirectiveSet();
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getDirectives() : \Graphpinator\Parser\Directive\DirectiveSet
    {
        return $this->directives;
    }
}
