<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Tests\Unit\Value;

use Graphpinator\Parser\Value\ArgumentValue;
use Graphpinator\Parser\Value\ListVal;
use Graphpinator\Parser\Value\Literal;
use Graphpinator\Parser\Value\Value;
use PHPUnit\Framework\TestCase;

final class NamedValueTest extends TestCase
{
    public static function simpleDataProvider() : array
    {
        return [
            [new Literal(123), 'name'],
            [new Literal(123.123), 'name'],
            [new Literal('123'), 'name'],
            [new Literal(true), 'name'],
            [new ListVal([]), 'name'],
        ];
    }

    /**
     * @dataProvider simpleDataProvider
     * @param Value $value
     * @param string $name
     */
    public function testSimple(Value $value, string $name) : void
    {
        $obj = new ArgumentValue($value, $name);

        self::assertSame($name, $obj->getName());
        self::assertSame($value, $obj->getValue());
        self::assertSame($value->getRawValue(), $obj->getValue()->getRawValue());
    }
}
