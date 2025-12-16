<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Tests\Unit\TypeRef;

use Graphpinator\Parser\TypeRef\ListTypeRef;
use Graphpinator\Parser\TypeRef\NamedTypeRef;
use Graphpinator\Parser\TypeRef\NotNullRef;
use PHPUnit\Framework\TestCase;

final class TypeRefTest extends TestCase
{
    public function testNamedTypeRefPrint() : void
    {
        $typeRef = new NamedTypeRef('String');
        
        self::assertSame('String', $typeRef->print());
    }
    
    public function testNotNullRefPrint() : void
    {
        $innerRef = new NamedTypeRef('Int');
        $typeRef = new NotNullRef($innerRef);
        
        self::assertSame('Int!', $typeRef->print());
    }
    
    public function testListTypeRefPrint() : void
    {
        $innerRef = new NamedTypeRef('String');
        $typeRef = new ListTypeRef($innerRef);
        
        self::assertSame('[String]', $typeRef->print());
    }
    
    public function testComplexTypeRefPrint() : void
    {
        $namedRef = new NamedTypeRef('Int');
        $notNullRef = new NotNullRef($namedRef);
        $listRef = new ListTypeRef($notNullRef);
        $outerNotNullRef = new NotNullRef($listRef);
        
        self::assertSame('[Int!]!', $outerNotNullRef->print());
    }
    
    public function testNestedListTypeRefPrint() : void
    {
        $namedRef = new NamedTypeRef('String');
        $innerListRef = new ListTypeRef($namedRef);
        $outerListRef = new ListTypeRef($innerListRef);
        
        self::assertSame('[[String]]', $outerListRef->print());
    }
}
