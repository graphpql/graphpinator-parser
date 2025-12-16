<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedSelectionSetBody extends ExpectedError
{
    public const MESSAGE = 'Expected field name or fragment, got "%s".';
}
