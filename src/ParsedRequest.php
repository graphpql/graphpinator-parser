<?php

declare(strict_types = 1);

namespace Graphpinator\Parser;

use Graphpinator\Parser\Fragment\FragmentSet;
use Graphpinator\Parser\Operation\OperationSet;

final class ParsedRequest
{
    public function __construct(
        private OperationSet $operations,
        private FragmentSet $fragments,
    )
    {
    }

    public function getOperations() : OperationSet
    {
        return $this->operations;
    }

    public function getFragments() : FragmentSet
    {
        return $this->fragments;
    }
}
