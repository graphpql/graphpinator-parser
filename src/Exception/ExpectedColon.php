<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedColon extends ExpectedError
{
    public const MESSAGE = 'Expected colon, got "%s".';
}
