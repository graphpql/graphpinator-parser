<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Tests\Unit\Value;

final class NamedValueTest extends \PHPUnit\Framework\TestCase
{
    public function simpleDataProvider() : array
    {
        return [
            [new \Graphpinator\Parser\Value\Literal(123), 'name'],
            [new \Graphpinator\Parser\Value\Literal(123.123), 'name'],
            [new \Graphpinator\Parser\Value\Literal('123'), 'name'],
            [new \Graphpinator\Parser\Value\Literal(true), 'name'],
            [new \Graphpinator\Parser\Value\ListVal([]), 'name'],
        ];
    }

    /**
     * @dataProvider simpleDataProvider
     * @param \Graphpinator\Parser\Value\Value $value
     * @param string $name
     */
    public function testSimple(\Graphpinator\Parser\Value\Value $value, string $name) : void
    {
        $obj = new \Graphpinator\Parser\Value\ArgumentValue($value, $name);

        self::assertSame($name, $obj->getName());
        self::assertSame($value, $obj->getValue());
        self::assertSame($value->getRawValue(), $obj->getValue()->getRawValue());
    }
}
