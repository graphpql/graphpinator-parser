<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

abstract class ParserError extends \Graphpinator\Exception\GraphpinatorBase
{
    public function __construct(\Graphpinator\Common\Location $location, array $messageArgs = [])
    {
        parent::__construct($messageArgs);

        $this->setLocation($location);
    }

    final public function isOutputable() : bool
    {
        return true;
    }
}
