<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Operation;

use Infinityloop\Utils\ImplicitObjectMap;

/**
 * @method Operation current() : object
 * @method Operation offsetGet($offset) : object
 */
final class OperationSet extends ImplicitObjectMap
{
    protected const INNER_CLASS = Operation::class;

    #[\Override]
    protected function getKey(object $object) : string
    {
        return $object->name
            ?? '';
    }
}
