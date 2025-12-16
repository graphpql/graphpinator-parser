<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Value;

use Infinityloop\Utils\ImplicitObjectMap;

/**
 * @method ArgumentValue current() : object
 * @method ArgumentValue offsetGet($offset) : object
 */
final class ArgumentValueSet extends ImplicitObjectMap
{
    protected const INNER_CLASS = ArgumentValue::class;

    protected function getKey(object $object) : string
    {
        return $object->getName(); // @phpstan-ignore method.notFound
    }
}
