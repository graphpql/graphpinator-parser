<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedType extends ExpectedError
{
    public const MESSAGE = 'Expected type reference, got "%s".';
}
