<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class MissingOperation extends \Graphpinator\Parser\Exception\ParserError
{
    public const MESSAGE = 'No GraphQL operation requested.';
}
