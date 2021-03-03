<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class DuplicateOperation extends \Graphpinator\Parser\Exception\ParserError
{
    public const MESSAGE = 'Operation with this name already exists in current request.';
}
