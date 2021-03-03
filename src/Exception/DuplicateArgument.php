<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

final class DuplicateArgument extends \Graphpinator\Parser\Exception\ParserError
{
    public const MESSAGE = 'Argument with name "%s" already exists on current field.';

    public function __construct(string $name, \Graphpinator\Common\Location $location)
    {
        $this->messageArgs = [$name];

        parent::__construct($location);
    }
}
