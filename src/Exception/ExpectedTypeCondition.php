<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedTypeCondition extends ExpectedError
{
    public const MESSAGE = 'Expected type condition for fragment, got "%s".';
}
