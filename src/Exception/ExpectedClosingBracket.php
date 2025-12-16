<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedClosingBracket extends ExpectedError
{
    public const MESSAGE = 'Expected closing ] for list type modifier, got "%s".';
}
