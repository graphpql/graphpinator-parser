<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class UnknownOperationType extends ParserError
{
    public const MESSAGE = 'Unknown operation type - one of: query, mutation, subscription (case-sensitive).';
}
