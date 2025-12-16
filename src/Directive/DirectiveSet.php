<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Directive;

use Infinityloop\Utils\ObjectSet;

/**
 * @method Directive current() : object
 * @method Directive offsetGet($offset) : object
 */
final class DirectiveSet extends ObjectSet
{
    protected const INNER_CLASS = Directive::class;
}
