<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedFragmentSpreadInfo extends ExpectedError
{
    public const MESSAGE = 'Expected fragment name or inline fragment, got "%s".';
}
