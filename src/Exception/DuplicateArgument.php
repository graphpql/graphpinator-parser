<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

use Graphpinator\Common\Location;

final class DuplicateArgument extends ParserError
{
    public const MESSAGE = 'Argument with name "%s" already exists on current field.';

    public function __construct(
        string $name,
        Location $location,
    )
    {
        parent::__construct($location, [$name]);
    }
}
