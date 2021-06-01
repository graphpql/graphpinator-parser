<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class DisallowedFragmentName extends \Graphpinator\Parser\Exception\ParserError
{
    public const MESSAGE = 'Invalid fragment name. Fragment cannot be named "on".';
}
