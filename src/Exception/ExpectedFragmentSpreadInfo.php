<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedFragmentSpreadInfo extends \Graphpinator\Parser\Exception\ExpectedError
{
    public const MESSAGE = 'Expected fragment name or inline fragment, got "%s".';
}
