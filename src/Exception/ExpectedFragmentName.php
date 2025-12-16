<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedFragmentName extends ExpectedError
{
    public const MESSAGE = 'Expected fragment name, got "%s".';
}
