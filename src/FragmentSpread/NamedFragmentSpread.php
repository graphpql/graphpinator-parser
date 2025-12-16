<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\FragmentSpread;

use Graphpinator\Parser\Directive\DirectiveSet;

final readonly class NamedFragmentSpread implements FragmentSpread
{
    public readonly DirectiveSet $directives;

    public function __construct(
        public string $name,
        ?DirectiveSet $directives = null,
    )
    {
        $this->directives = $directives
            ?? new DirectiveSet();
    }
}
