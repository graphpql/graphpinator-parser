<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Tests\Unit\FragmentSpread;

use Graphpinator\Parser\FragmentSpread\NamedFragmentSpread;
use PHPUnit\Framework\TestCase;

final class NamedFragmentSpreadTest extends TestCase
{
    public function testConstructor() : void
    {
        $val = new NamedFragmentSpread('fragment');
        self::assertSame('fragment', $val->getName());
        self::assertCount(0, $val->getDirectives());
    }
}
