<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedSelectionSet extends \Graphpinator\Parser\Exception\ExpectedError
{
    public const MESSAGE = 'Expected selection set, got "%s".';
}
