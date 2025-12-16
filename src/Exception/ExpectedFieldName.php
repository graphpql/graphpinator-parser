<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedFieldName extends ExpectedError
{
    public const MESSAGE = 'Expected field name, got "%s".';
}
