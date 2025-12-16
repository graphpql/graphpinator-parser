<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Tests\Unit\Exception;

use Graphpinator\Common\Location;
use Graphpinator\Parser\Exception\EmptyRequest;
use PHPUnit\Framework\TestCase;

final class ParserErrorTest extends TestCase
{
    public function testIsOutputable() : void
    {
        $exception = new EmptyRequest(new Location(1, 1));
        
        self::assertTrue($exception->isOutputable());
    }
    
    public function testLocationIsSet() : void
    {
        $location = new Location(5, 10);
        $exception = new EmptyRequest($location);
        
        self::assertSame($location, $exception->getLocation());
    }
}
