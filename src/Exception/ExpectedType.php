<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedType extends \Graphpinator\Parser\Exception\ExpectedError
{
    public const MESSAGE = 'Expected type reference, got "%s".';
}
