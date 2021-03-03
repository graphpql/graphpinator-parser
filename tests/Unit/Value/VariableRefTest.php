<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Tests\Unit\Value;

final class VariableRefTest extends \PHPUnit\Framework\TestCase
{
    public function testGetRawValue() : void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Operation not supported.');

        $val = new \Graphpinator\Parser\Value\VariableRef('varName');
        $val->getRawValue();
    }
}
