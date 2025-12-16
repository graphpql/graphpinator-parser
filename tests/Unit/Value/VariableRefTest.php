<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Tests\Unit\Value;

use Graphpinator\Parser\Value\VariableRef;
use PHPUnit\Framework\TestCase;

final class VariableRefTest extends TestCase
{
    public function testGetRawValue() : void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Operation not supported.');

        $val = new VariableRef('varName');
        $val->getRawValue();
    }
}
