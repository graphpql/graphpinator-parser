<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedRoot extends ExpectedError
{
    public const MESSAGE = 'Expected operation or fragment definition, got "%s".';
}
