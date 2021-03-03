<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class ExpectedNamedType extends \Graphpinator\Parser\Exception\ExpectedError
{
    public const MESSAGE = 'Expected named type without type modifiers, got "%s".';
}
