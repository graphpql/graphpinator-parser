<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Tests\Unit\FragmentSpread;

use Graphpinator\Parser\Field\FieldSet;
use Graphpinator\Parser\FragmentSpread\FragmentSpreadSet;
use Graphpinator\Parser\FragmentSpread\InlineFragmentSpread;
use PHPUnit\Framework\TestCase;

final class InlineFragmentSpreadTest extends TestCase
{
    public function testConstructor() : void
    {
        $val = new InlineFragmentSpread(
            new FieldSet([], new FragmentSpreadSet()),
        );
        self::assertCount(0, $val->fields);
        self::assertCount(0, $val->directives);
    }
}
