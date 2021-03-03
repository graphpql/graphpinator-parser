<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedArgumentName extends \Graphpinator\Parser\Exception\ExpectedError
{
    public const MESSAGE = 'Expected argument or closing parenthesis, got "%s".';
}
