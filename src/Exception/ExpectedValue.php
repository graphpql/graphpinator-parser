<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedValue extends ExpectedError
{
    public const MESSAGE = 'Expected value - either literal or variable reference, got "%s".';
}
