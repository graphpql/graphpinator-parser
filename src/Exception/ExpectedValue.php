<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedValue extends \Graphpinator\Parser\Exception\ExpectedError
{
    public const MESSAGE = 'Expected value - either literal or variable reference, got "%s".';
}
