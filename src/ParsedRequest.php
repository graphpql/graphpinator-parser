<?php

declare(strict_types = 1);

namespace Graphpinator\Parser;

use Graphpinator\Parser\Fragment\FragmentSet;
use Graphpinator\Parser\Operation\OperationSet;

final readonly class ParsedRequest
{
    public function __construct(
        public OperationSet $operations,
        public FragmentSet $fragments,
    )
    {
    }
}
