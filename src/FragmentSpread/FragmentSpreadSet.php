<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\FragmentSpread;

use Infinityloop\Utils\ObjectSet;

/**
 * @method FragmentSpread current() : object
 * @method FragmentSpread offsetGet($offset) : object
 */
final class FragmentSpreadSet extends ObjectSet
{
    protected const INNER_CLASS = FragmentSpread::class;
}
