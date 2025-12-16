<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class EmptyRequest extends ParserError
{
    public const MESSAGE = 'Request is empty.';
}
