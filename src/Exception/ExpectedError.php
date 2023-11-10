<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Exception;

abstract class ExpectedError extends \Graphpinator\Parser\Exception\ParserError
{
    final public function __construct(\Graphpinator\Common\Location $location, \Graphpinator\Tokenizer\TokenType $token)
    {
        parent::__construct($location, [$token->value]);
    }
}
