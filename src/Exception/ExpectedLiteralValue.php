<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedLiteralValue extends ExpectedError
{
    public const MESSAGE = 'Expected literal value as variable default value, got "%s".';
}
