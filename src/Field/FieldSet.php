<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Field;

use Graphpinator\Parser\FragmentSpread\FragmentSpreadSet;
use Infinityloop\Utils\ObjectSet;

/**
 * @method Field current() : object
 * @method Field offsetGet($offset) : object
 */
final class FieldSet extends ObjectSet
{
    protected const INNER_CLASS = Field::class;

    /**
     * @param list<Field> $fields
     */
    public function __construct(
        array $fields,
        private FragmentSpreadSet $fragments,
    )
    {
        parent::__construct($fields);
    }

    public function getFragmentSpreads() : FragmentSpreadSet
    {
        return $this->fragments;
    }
}
