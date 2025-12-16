<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

use Graphpinator\Common\Location;
use Graphpinator\Exception\GraphpinatorBase;

abstract class ParserError extends GraphpinatorBase
{
    /**
     * @param Location $location
     * @param list<string> $messageArgs
     */
    public function __construct(
        Location $location,
        array $messageArgs = [],
    )
    {
        parent::__construct($messageArgs);

        $this->setLocation($location);
    }

    #[\Override]
    final public function isOutputable() : bool
    {
        return true;
    }
}
