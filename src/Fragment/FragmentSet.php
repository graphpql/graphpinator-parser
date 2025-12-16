<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Fragment;

use Infinityloop\Utils\ImplicitObjectMap;

/**
 * @method Fragment current() : object
 * @method Fragment offsetGet($offset) : object
 */
final class FragmentSet extends ImplicitObjectMap
{
    protected const INNER_CLASS = Fragment::class;

    protected function getKey(object $object) : string
    {
        return $object->getName(); // @phpstan-ignore method.notFound
    }
}
