<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

use Graphpinator\Common\Location;
use Graphpinator\Tokenizer\TokenType;

abstract class ExpectedError extends ParserError
{
    final public function __construct(
        Location $location,
        TokenType $token,
    )
    {
        parent::__construct($location, [$token->value]);
    }
}
