<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedVariableName extends \Graphpinator\Parser\Exception\ExpectedError
{
    public const MESSAGE = 'Expected variable or closing parenthesis, got "%s".';
}
