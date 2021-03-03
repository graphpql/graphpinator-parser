<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedFragmentName extends \Graphpinator\Parser\Exception\ExpectedError
{
    public const MESSAGE = 'Expected fragment name, got "%s".';
}
