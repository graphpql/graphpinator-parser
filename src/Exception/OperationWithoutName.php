<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class OperationWithoutName extends ParserError
{
    public const MESSAGE = 'Multiple operations given, but not all have specified name.';
}
