<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Tests\Unit;

final class ParserTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor() : void
    {
        $source = new \Graphpinator\Source\StringSource('query queryName { field }');
        $parser = new \Graphpinator\Parser\Parser();
        $result = $parser->parse($source);

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());
        self::assertCount(0, $result->getOperations()->current()->getVariables());
        self::assertCount(1, $result->getOperations()->current()->getFields());
        self::assertSame('query', $result->getOperations()->current()->getType());
        self::assertSame('queryName', $result->getOperations()->current()->getName());
    }

    public function testQuery() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName { field }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());
        self::assertCount(0, $result->getOperations()->current()->getVariables());
        self::assertCount(1, $result->getOperations()->current()->getFields());
        self::assertSame('query', $result->getOperations()->current()->getType());
        self::assertSame('queryName', $result->getOperations()->current()->getName());
    }

    public function testMutation() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('mutation mutName { field }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());
        self::assertCount(0, $result->getOperations()->current()->getVariables());
        self::assertCount(1, $result->getOperations()->current()->getFields());
        self::assertSame('mutation', $result->getOperations()->current()->getType());
        self::assertSame('mutName', $result->getOperations()->current()->getName());
    }

    public function testSubscription() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('subscription subName { field }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());
        self::assertCount(0, $result->getOperations()->current()->getVariables());
        self::assertCount(1, $result->getOperations()->current()->getFields());
        self::assertSame('subscription', $result->getOperations()->current()->getType());
        self::assertSame('subName', $result->getOperations()->current()->getName());
    }

    public function testQueryNoName() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query { field }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());
        self::assertCount(0, $result->getOperations()->current()->getVariables());
        self::assertCount(1, $result->getOperations()->current()->getFields());
        self::assertSame('query', $result->getOperations()->current()->getType());
        self::assertNull($result->getOperations()->current()->getName());
    }

    public function testQueryShorthand() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('{ field }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());
        self::assertCount(0, $result->getOperations()->current()->getVariables());
        self::assertCount(1, $result->getOperations()->current()->getFields());
        self::assertSame('query', $result->getOperations()->current()->getType());
        self::assertNull($result->getOperations()->current()->getName());
    }

    public function testQueryMultiple() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query qName { field } mutation mName { field }');

        self::assertCount(0, $result->getFragments());
    }

    public function testDirective() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query { field @directiveName(arg1: 123) }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(1, $operation->getFields());
        self::assertArrayHasKey(0, $operation->getFields());
        self::assertCount(1, $operation->getFields()->offsetGet(0)->getDirectives());
        self::assertArrayHasKey(0, $operation->getFields()->offsetGet(0)->getDirectives());
        self::assertSame('directiveName', $operation->getFields()->offsetGet(0)->getDirectives()->offsetGet(0)->getName());
        self::assertCount(1, $operation->getFields()->offsetGet(0)->getDirectives()->offsetGet(0)->getArguments());
        self::assertArrayHasKey('arg1', $operation->getFields()->offsetGet(0)->getDirectives()->offsetGet(0)->getArguments());
        self::assertSame(
            'arg1',
            $operation
                ->getFields()
                ->offsetGet(0)
                ->getDirectives()
                ->offsetGet(0)
                ->getArguments()
                ->offsetGet('arg1')
                ->getName(),
        );
        self::assertSame(
            123,
            $operation
                ->getFields()
                ->offsetGet(0)
                ->getDirectives()
                ->offsetGet(0)
                ->getArguments()
                ->offsetGet('arg1')
                ->getValue()
                ->getRawValue(),
        );
    }

    public function testFragment() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('fragment fragmentName on TypeName { field } query queryName { field }');

        self::assertCount(1, $result->getFragments());
        self::assertCount(1, $result->getOperations());
        self::assertArrayHasKey('fragmentName', $result->getFragments());
        self::assertSame('fragmentName', $result->getFragments()->offsetGet('fragmentName')->getName());
        self::assertSame('TypeName', $result->getFragments()->offsetGet('fragmentName')->getTypeCond()->getName());
        self::assertCount(1, $result->getFragments()->offsetGet('fragmentName')->getFields());
        self::assertCount(1, $result->getFragments()->offsetGet('fragmentName')->getFields());
        self::assertCount(0, $result->getOperations()->current()->getVariables());
        self::assertCount(1, $result->getOperations()->current()->getFields());
        self::assertSame('query', $result->getOperations()->current()->getType());
        self::assertSame('queryName', $result->getOperations()->current()->getName());
        self::assertCount(0, $result->getFragments()->offsetGet('fragmentName')->getDirectives());
    }

    public function testFragmentDirectives() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('fragment fragmentName on TypeName @abc { field } query { field }');

        self::assertCount(1, $result->getFragments());
        self::assertCount(1, $result->getOperations());
        self::assertArrayHasKey('fragmentName', $result->getFragments());
        self::assertSame('fragmentName', $result->getFragments()->offsetGet('fragmentName')->getName());
        self::assertSame('TypeName', $result->getFragments()->offsetGet('fragmentName')->getTypeCond()->getName());
        self::assertCount(1, $result->getFragments()->offsetGet('fragmentName')->getFields());
        self::assertCount(1, $result->getFragments()->offsetGet('fragmentName')->getFields());
        self::assertCount(0, $result->getOperations()->current()->getVariables());
        self::assertCount(1, $result->getOperations()->current()->getFields());
        self::assertSame('query', $result->getOperations()->current()->getType());
        self::assertNull($result->getOperations()->current()->getName());
        self::assertCount(1, $result->getFragments()->offsetGet('fragmentName')->getDirectives());
        self::assertSame('abc', $result->getFragments()->offsetGet('fragmentName')->getDirectives()->offsetGet(0)->getName());
    }

    public function testNamedFragmentSpread() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query { ... fragmentName } ');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(0, $operation->getFields());
        self::assertCount(1, $operation->getFields()->getFragmentSpreads());
        self::assertArrayHasKey(0, $operation->getFields()->getFragmentSpreads());
        self::assertInstanceOf(
            \Graphpinator\Parser\FragmentSpread\NamedFragmentSpread::class,
            $operation->getFields()->getFragmentSpreads()[0],
        );
        self::assertSame('fragmentName', $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getName());
        self::assertCount(0, $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getDirectives());
    }

    public function testInlineFragmentSpread() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query { ... on TypeName { fieldName } }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(0, $operation->getFields());
        self::assertCount(1, $operation->getFields()->getFragmentSpreads());
        self::assertArrayHasKey(0, $operation->getFields()->getFragmentSpreads());
        self::assertInstanceOf(
            \Graphpinator\Parser\FragmentSpread\InlineFragmentSpread::class,
            $operation->getFields()->getFragmentSpreads()[0],
        );
        self::assertSame('TypeName', $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getTypeCond()->getName());
        self::assertCount(1, $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getFields());
        self::assertCount(0, $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getDirectives());
    }

    public function testNamedFragmentSpreadDirective() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query { ... fragmentName @directiveName() }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(0, $operation->getFields());
        self::assertCount(1, $operation->getFields()->getFragmentSpreads());
        self::assertArrayHasKey(0, $operation->getFields()->getFragmentSpreads());
        self::assertInstanceOf(
            \Graphpinator\Parser\FragmentSpread\NamedFragmentSpread::class,
            $operation->getFields()->getFragmentSpreads()[0],
        );
        self::assertSame('fragmentName', $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getName());
        self::assertCount(1, $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getDirectives());
        self::assertArrayHasKey(0, $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getDirectives());
        self::assertSame(
            'directiveName',
            $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getDirectives()->offsetGet(0)->getName(),
        );
    }

    public function testInlineFragmentSpreadDirective() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query { ... @directiveName() { fieldName } }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(0, $operation->getFields());
        self::assertCount(1, $operation->getFields()->getFragmentSpreads());
        self::assertArrayHasKey(0, $operation->getFields()->getFragmentSpreads());
        self::assertInstanceOf(
            \Graphpinator\Parser\FragmentSpread\InlineFragmentSpread::class,
            $operation->getFields()->getFragmentSpreads()[0],
        );
        self::assertNull($operation->getFields()->getFragmentSpreads()->offsetGet(0)->getTypeCond());
        self::assertCount(1, $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getFields());
        self::assertCount(1, $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getDirectives());
        self::assertArrayHasKey(0, $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getDirectives());
        self::assertSame(
            'directiveName',
            $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getDirectives()->offsetGet(0)->getName(),
        );
    }

    public function testInlineFragmentSpreadBoth() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query { ... on TypeName @directiveName() { fieldName } }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(0, $operation->getFields());
        self::assertCount(1, $operation->getFields()->getFragmentSpreads());
        self::assertArrayHasKey(0, $operation->getFields()->getFragmentSpreads());
        self::assertInstanceOf(
            \Graphpinator\Parser\FragmentSpread\InlineFragmentSpread::class,
            $operation->getFields()->getFragmentSpreads()[0],
        );
        self::assertSame('TypeName', $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getTypeCond()->getName());
        self::assertCount(1, $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getFields());
        self::assertCount(1, $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getDirectives());
        self::assertArrayHasKey(0, $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getDirectives());
        self::assertSame(
            'directiveName',
            $operation->getFields()->getFragmentSpreads()->offsetGet(0)->getDirectives()->offsetGet(0)->getName(),
        );
    }

    public function testVariable() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName ($varName: Int) { field }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertSame('queryName', $operation->getName());
        self::assertCount(1, $operation->getFields());
        self::assertCount(1, $operation->getVariables());
        self::assertCount(0, $operation->getDirectives());
        self::assertArrayHasKey('varName', $operation->getVariables());
        self::assertSame('varName', $operation->getVariables()->offsetGet('varName')->getName());
        self::assertInstanceOf(
            \Graphpinator\Parser\TypeRef\NamedTypeRef::class,
            $operation->getVariables()->offsetGet('varName')->getType(),
        );
        self::assertSame('Int', $operation->getVariables()->offsetGet('varName')->getType()->getName());
        self::assertNull($operation->getVariables()->offsetGet('varName')->getDefault());
    }

    public function testVariableNoName() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query ($varName: Int) { field }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertNull($operation->getName());
        self::assertCount(1, $operation->getFields());
        self::assertCount(1, $operation->getVariables());
        self::assertCount(0, $operation->getDirectives());
        self::assertArrayHasKey('varName', $operation->getVariables());
        self::assertSame('varName', $operation->getVariables()->offsetGet('varName')->getName());
        self::assertInstanceOf(
            \Graphpinator\Parser\TypeRef\NamedTypeRef::class,
            $operation->getVariables()->offsetGet('varName')->getType(),
        );
        self::assertSame('Int', $operation->getVariables()->offsetGet('varName')->getType()->getName());
        self::assertNull($operation->getVariables()->offsetGet('varName')->getDefault());
    }

    public function testDirectiveNoName() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query @directive { field }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertNull($operation->getName());
        self::assertCount(1, $operation->getFields());
        self::assertCount(0, $operation->getVariables());
        self::assertCount(1, $operation->getDirectives());
        self::assertArrayHasKey(0, $operation->getDirectives());
        self::assertSame('directive', $operation->getDirectives()->offsetGet(0)->getName());
        self::assertNull($operation->getDirectives()->offsetGet(0)->getArguments());
    }

    public function testVariableDefault() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName ($varName: Float = 3.14) { field }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(1, $operation->getFields());
        self::assertCount(1, $operation->getVariables());
        self::assertArrayHasKey('varName', $operation->getVariables());
        self::assertSame('varName', $operation->getVariables()->offsetGet('varName')->getName());
        self::assertInstanceOf(
            \Graphpinator\Parser\TypeRef\NamedTypeRef::class,
            $operation->getVariables()->offsetGet('varName')->getType(),
        );
        self::assertSame('Float', $operation->getVariables()->offsetGet('varName')->getType()->getName());
        self::assertSame(3.14, $operation->getVariables()->offsetGet('varName')->getDefault()->getRawValue());
    }

    public function testVariableComplexType() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName ($varName: [Int!]!) { field }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(1, $operation->getFields());
        self::assertCount(1, $operation->getVariables());
        self::assertArrayHasKey('varName', $operation->getVariables());
        self::assertSame('varName', $operation->getVariables()->offsetGet('varName')->getName());
        self::assertInstanceOf(
            \Graphpinator\Parser\TypeRef\NotNullRef::class,
            $operation->getVariables()->offsetGet('varName')->getType(),
        );
        self::assertInstanceOf(
            \Graphpinator\Parser\TypeRef\ListTypeRef::class,
            $operation->getVariables()->offsetGet('varName')->getType()->getInnerRef(),
        );
        self::assertInstanceOf(
            \Graphpinator\Parser\TypeRef\NotNullRef::class,
            $operation->getVariables()->offsetGet('varName')->getType()->getInnerRef()->getInnerRef(),
        );
        self::assertInstanceOf(
            \Graphpinator\Parser\TypeRef\NamedTypeRef::class,
            $operation->getVariables()->offsetGet('varName')->getType()->getInnerRef()->getInnerRef()->getInnerRef(),
        );
        self::assertSame(
            'Int',
            $operation->getVariables()->offsetGet('varName')->getType()->getInnerRef()->getInnerRef()->getInnerRef()->getName(),
        );
    }

    public function testVariableMultiple() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName ($varName: Boolean = true, $varName2: Boolean!) { field }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(1, $operation->getFields());
        self::assertCount(2, $operation->getVariables());
        self::assertArrayHasKey('varName', $operation->getVariables());
        self::assertArrayHasKey('varName2', $operation->getVariables());
        self::assertSame('varName', $operation->getVariables()->offsetGet('varName')->getName());
        self::assertSame('varName2', $operation->getVariables()->offsetGet('varName2')->getName());
        self::assertInstanceOf(
            \Graphpinator\Parser\TypeRef\NamedTypeRef::class,
            $operation->getVariables()->offsetGet('varName')->getType(),
        );
        self::assertSame('Boolean', $operation->getVariables()->offsetGet('varName')->getType()->getName());
        self::assertTrue($operation->getVariables()->offsetGet('varName')->getDefault()->getRawValue());
        self::assertInstanceOf(
            \Graphpinator\Parser\TypeRef\NotNullRef::class,
            $operation->getVariables()->offsetGet('varName2')->getType(),
        );
        self::assertInstanceOf(
            \Graphpinator\Parser\TypeRef\NamedTypeRef::class,
            $operation->getVariables()->offsetGet('varName2')->getType()->getInnerRef(),
        );
        self::assertSame('Boolean', $operation->getVariables()->offsetGet('varName2')->getType()->getInnerRef()->getName());
        self::assertNull($operation->getVariables()->offsetGet('varName2')->getDefault());
    }

    public function testVariableDefaultList() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName ($varName: [Bool] = [true, false]) { field }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(1, $operation->getFields());
        self::assertCount(1, $operation->getVariables());
        self::assertArrayHasKey('varName', $operation->getVariables());
        self::assertSame('varName', $operation->getVariables()->offsetGet('varName')->getName());
        self::assertInstanceOf(
            \Graphpinator\Parser\TypeRef\ListTypeRef::class,
            $operation->getVariables()->offsetGet('varName')->getType(),
        );
        self::assertInstanceOf(
            \Graphpinator\Parser\TypeRef\NamedTypeRef::class,
            $operation->getVariables()->offsetGet('varName')->getType()->getInnerRef(),
        );
        self::assertSame('Bool', $operation->getVariables()->offsetGet('varName')->getType()->getInnerRef()->getName());
        self::assertSame([true, false], $operation->getVariables()->offsetGet('varName')->getDefault()->getRawValue());
        self::assertCount(0, $operation->getVariables()->offsetGet('varName')->getDirectives());
    }

    public function testVariableDefaultObject() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName ($varName: InputType = {fieldName: null, fieldName2: {}}) { field }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(1, $operation->getFields());
        self::assertCount(1, $operation->getVariables());
        self::assertArrayHasKey('varName', $operation->getVariables());
        self::assertSame('varName', $operation->getVariables()->offsetGet('varName')->getName());
        self::assertInstanceOf(
            \Graphpinator\Parser\TypeRef\NamedTypeRef::class,
            $operation->getVariables()->offsetGet('varName')->getType(),
        );
        self::assertSame('InputType', $operation->getVariables()->offsetGet('varName')->getType()->getName());
        self::assertEquals(
            (object) [
                'fieldName' => null,
                'fieldName2' => (object) [],
            ],
            $operation->getVariables()->offsetGet('varName')->getDefault()->getRawValue(),
        );
        self::assertCount(0, $operation->getVariables()->offsetGet('varName')->getDirectives());
    }

    public function testVariableDefaultEnum() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName ($varName: EnumType = ENUM_VALUE) { field }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(1, $operation->getFields());
        self::assertCount(1, $operation->getVariables());
        self::assertArrayHasKey('varName', $operation->getVariables());
        self::assertSame('varName', $operation->getVariables()->offsetGet('varName')->getName());
        self::assertInstanceOf(
            \Graphpinator\Parser\TypeRef\NamedTypeRef::class,
            $operation->getVariables()->offsetGet('varName')->getType(),
        );
        self::assertSame('EnumType', $operation->getVariables()->offsetGet('varName')->getType()->getName());
        self::assertEquals(
            'ENUM_VALUE',
            $operation->getVariables()->offsetGet('varName')->getDefault()->getRawValue(),
        );
        self::assertCount(0, $operation->getVariables()->offsetGet('varName')->getDirectives());
    }

    public function testVariableDirectives() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName ($varName: Int @abc) { field }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(1, $operation->getFields());
        self::assertCount(1, $operation->getVariables());
        self::assertArrayHasKey('varName', $operation->getVariables());
        self::assertSame('varName', $operation->getVariables()->offsetGet('varName')->getName());
        self::assertInstanceOf(
            \Graphpinator\Parser\TypeRef\NamedTypeRef::class,
            $operation->getVariables()->offsetGet('varName')->getType(),
        );
        self::assertSame('Int', $operation->getVariables()->offsetGet('varName')->getType()->getName());
        self::assertCount(1, $operation->getVariables()->offsetGet('varName')->getDirectives());
        self::assertSame('abc', $operation->getVariables()->offsetGet('varName')->getDirectives()->offsetGet(0)->getName());
    }

    public function testVariableUsage() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName ($varName: Int) { field(arg: $varName) }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertSame('queryName', $operation->getName());
        self::assertCount(1, $operation->getFields());
        self::assertSame('field', $operation->getFields()->offsetGet(0)->getName());
        self::assertCount(1, $operation->getFields()->offsetGet(0)->getArguments());
        self::assertSame('arg', $operation->getFields()->offsetGet(0)->getArguments()->offsetGet('arg')->getName());
        self::assertInstanceOf(
            \Graphpinator\Parser\Value\VariableRef::class,
            $operation->getFields()->offsetGet(0)->getArguments()->offsetGet('arg')->getValue(),
        );
        self::assertSame('varName', $operation->getFields()->offsetGet(0)->getArguments()->offsetGet('arg')->getValue()->getVarName());
        self::assertCount(1, $operation->getVariables());
        self::assertCount(0, $operation->getDirectives());
        self::assertArrayHasKey('varName', $operation->getVariables());
        self::assertSame('varName', $operation->getVariables()->offsetGet('varName')->getName());
        self::assertInstanceOf(
            \Graphpinator\Parser\TypeRef\NamedTypeRef::class,
            $operation->getVariables()->offsetGet('varName')->getType(),
        );
        self::assertSame('Int', $operation->getVariables()->offsetGet('varName')->getType()->getName());
        self::assertNull($operation->getVariables()->offsetGet('varName')->getDefault());
    }

    public function testField() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName { fieldName }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(1, $operation->getFields());
        self::assertCount(0, $operation->getVariables());
        self::assertArrayHasKey(0, $operation->getFields());
        self::assertSame('fieldName', $operation->getFields()->offsetGet(0)->getName());
        self::assertNull($operation->getFields()->offsetGet(0)->getAlias());
        self::assertNull($operation->getFields()->offsetGet(0)->getArguments());
        self::assertNull($operation->getFields()->offsetGet(0)->getFields());
    }

    public function testFieldArguments() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName { fieldName(argName: "argVal") }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(0, $operation->getVariables());
        self::assertCount(1, $operation->getFields());
        self::assertArrayHasKey(0, $operation->getFields());
        self::assertSame('fieldName', $operation->getFields()->offsetGet(0)->getName());
        self::assertNull($operation->getFields()->offsetGet(0)->getAlias());
        self::assertInstanceOf(
            \Graphpinator\Parser\Value\ArgumentValueSet::class,
            $operation->getFields()->offsetGet(0)->getArguments(),
        );
        self::assertCount(1, $operation->getFields()->offsetGet(0)->getArguments());
        self::assertArrayHasKey('argName', $operation->getFields()->offsetGet(0)->getArguments());
        self::assertSame('argVal', $operation->getFields()->offsetGet(0)->getArguments()->offsetGet('argName')->getValue()->getRawValue());
        self::assertNull($operation->getFields()->offsetGet(0)->getFields());
    }

    public function testFieldArgumentsKeywordName() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName { type(input: "argVal") }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(0, $operation->getVariables());
        self::assertCount(1, $operation->getFields());
        self::assertArrayHasKey(0, $operation->getFields());
        self::assertSame('type', $operation->getFields()->offsetGet(0)->getName());
        self::assertNull($operation->getFields()->offsetGet(0)->getAlias());
        self::assertInstanceOf(
            \Graphpinator\Parser\Value\ArgumentValueSet::class,
            $operation->getFields()->offsetGet(0)->getArguments(),
        );
        self::assertCount(1, $operation->getFields()->offsetGet(0)->getArguments());
        self::assertArrayHasKey('input', $operation->getFields()->offsetGet(0)->getArguments());
        self::assertSame('argVal', $operation->getFields()->offsetGet(0)->getArguments()->offsetGet('input')->getValue()->getRawValue());
        self::assertNull($operation->getFields()->offsetGet(0)->getFields());
    }

    public function testFieldSubfield() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName { fieldName { innerField } }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(0, $operation->getVariables());
        self::assertCount(1, $operation->getFields());
        self::assertArrayHasKey(0, $operation->getFields());
        self::assertSame('fieldName', $operation->getFields()->offsetGet(0)->getName());
        self::assertNull($operation->getFields()->offsetGet(0)->getAlias());
        self::assertNull($operation->getFields()->offsetGet(0)->getArguments());
        self::assertInstanceOf(\Graphpinator\Parser\Field\FieldSet::class, $operation->getFields()->offsetGet(0)->getFields());
        self::assertCount(1, $operation->getFields()->offsetGet(0)->getFields());
        self::assertArrayHasKey(0, $operation->getFields()->offsetGet(0)->getFields());
        self::assertSame('innerField', $operation->getFields()->offsetGet(0)->getFields()->offsetGet(0)->getName());
        self::assertNull($operation->getFields()->offsetGet(0)->getFields()->offsetGet(0)->getAlias());
        self::assertNull($operation->getFields()->offsetGet(0)->getFields()->offsetGet(0)->getArguments());
    }

    public function testFieldAlias() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('query queryName { aliasName: fieldName }');

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(1, $operation->getFields());
        self::assertCount(0, $operation->getVariables());
        self::assertArrayHasKey(0, $operation->getFields());
        self::assertSame('fieldName', $operation->getFields()->offsetGet(0)->getName());
        self::assertSame('aliasName', $operation->getFields()->offsetGet(0)->getAlias());
        self::assertNull($operation->getFields()->offsetGet(0)->getArguments());
        self::assertNull($operation->getFields()->offsetGet(0)->getFields());
    }

    public function testFieldAll() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString(
            'query queryName { aliasName: fieldName(argName: "argVal") { innerField(argName: 12.34) }}',
        );

        self::assertCount(0, $result->getFragments());
        self::assertCount(1, $result->getOperations());

        $operation = $result->getOperations()->current();

        self::assertCount(0, $operation->getVariables());
        self::assertCount(1, $operation->getFields());
        self::assertArrayHasKey(0, $operation->getFields());
        self::assertSame('fieldName', $operation->getFields()->offsetGet(0)->getName());
        self::assertSame('aliasName', $operation->getFields()->offsetGet(0)->getAlias());
        self::assertInstanceOf(
            \Graphpinator\Parser\Value\ArgumentValueSet::class,
            $operation->getFields()->offsetGet(0)->getArguments(),
        );
        self::assertCount(1, $operation->getFields()->offsetGet(0)->getArguments());
        self::assertArrayHasKey('argName', $operation->getFields()->offsetGet(0)->getArguments());
        self::assertInstanceOf(\Graphpinator\Parser\Field\FieldSet::class, $operation->getFields()->offsetGet(0)->getFields());
        self::assertCount(1, $operation->getFields()->offsetGet(0)->getFields());
        self::assertArrayHasKey(0, $operation->getFields()->offsetGet(0)->getFields());
        self::assertSame('innerField', $operation->getFields()->offsetGet(0)->getFields()->offsetGet(0)->getName());
        self::assertNull($operation->getFields()->offsetGet(0)->getFields()->offsetGet(0)->getAlias());
        self::assertInstanceOf(
            \Graphpinator\Parser\Value\ArgumentValueSet::class,
            $operation->getFields()->offsetGet(0)->getFields()->offsetGet(0)->getArguments(),
        );
        self::assertCount(1, $operation->getFields()->offsetGet(0)->getFields()->offsetGet(0)->getArguments());
    }

    public function testMultipleOperations() : void
    {
        $result = \Graphpinator\Parser\Parser::parseString('
            query queryName { aliasName: fieldName(argName: "argVal") { innerField(argName: 12.34) }}
            query anotherQuery { fieldName(argName: "argVal2") { innerField(argName: 12.35) }}
            query lastQuery { fieldName(argName: "argVal3") { innerField(argName: 12.36) }}
        ');

        self::assertCount(0, $result->getFragments());
        self::assertCount(3, $result->getOperations());

        $operation = $result->getOperations()['queryName'];

        self::assertCount(0, $operation->getVariables());
        self::assertCount(1, $operation->getFields());
        self::assertArrayHasKey(0, $operation->getFields());
        self::assertSame('fieldName', $operation->getFields()->offsetGet(0)->getName());
        self::assertSame('aliasName', $operation->getFields()->offsetGet(0)->getAlias());
        self::assertInstanceOf(
            \Graphpinator\Parser\Value\ArgumentValueSet::class,
            $operation->getFields()->offsetGet(0)->getArguments(),
        );
        self::assertCount(1, $operation->getFields()->offsetGet(0)->getArguments());
        self::assertArrayHasKey('argName', $operation->getFields()->offsetGet(0)->getArguments());
        self::assertInstanceOf(\Graphpinator\Parser\Field\FieldSet::class, $operation->getFields()->offsetGet(0)->getFields());
        self::assertCount(1, $operation->getFields()->offsetGet(0)->getFields());
        self::assertArrayHasKey(0, $operation->getFields()->offsetGet(0)->getFields());
        self::assertSame('innerField', $operation->getFields()->offsetGet(0)->getFields()->offsetGet(0)->getName());
        self::assertNull($operation->getFields()->offsetGet(0)->getFields()->offsetGet(0)->getAlias());
        self::assertInstanceOf(
            \Graphpinator\Parser\Value\ArgumentValueSet::class,
            $operation->getFields()->offsetGet(0)->getFields()->offsetGet(0)->getArguments(),
        );
        self::assertCount(1, $operation->getFields()->offsetGet(0)->getFields()->offsetGet(0)->getArguments());
    }

    public static function invalidDataProvider() : array
    {
        return [
            ['', \Graphpinator\Parser\Exception\EmptyRequest::class],
            ['query', \Graphpinator\Parser\Exception\UnexpectedEnd::class],
            [
                '{}',
                \Graphpinator\Parser\Exception\ExpectedSelectionSetBody::class,
                'Expected field name or fragment, got "}".',
            ],
            [
                '{   }',
                \Graphpinator\Parser\Exception\ExpectedSelectionSetBody::class,
                'Expected field name or fragment, got "}".',
            ],
            [
                'query {}',
                \Graphpinator\Parser\Exception\ExpectedSelectionSetBody::class,
                'Expected field name or fragment, got "}".',
            ],
            [
                '$var',
                \Graphpinator\Parser\Exception\ExpectedRoot::class,
                'Expected operation or fragment definition, got "$".',
            ],
            [
                'fragment fragmentName on TypeName { f }',
                \Graphpinator\Parser\Exception\MissingOperation::class,
            ],
            [
                'fragment fragmentName on TypeName! { f }',
                \Graphpinator\Parser\Exception\ExpectedSelectionSet::class,
                'Expected selection set, got "!".',
            ],
            [
                'fragment fragmentName on [TypeName] { f }',
                \Graphpinator\Parser\Exception\ExpectedNamedType::class,
                'Expected named type without type modifiers, got "[".',
            ],
            [
                'fragment fragmentName { f }',
                \Graphpinator\Parser\Exception\ExpectedTypeCondition::class,
                'Expected type condition for fragment, got "{".',
            ],
            [
                'fragment fragmentName on { f }',
                \Graphpinator\Parser\Exception\ExpectedNamedType::class,
                'Expected named type without type modifiers, got "{".',
            ],
            [
                'queryName { f }',
                \Graphpinator\Parser\Exception\UnknownOperationType::class,
            ],
            [
                'queary queryName { f }',
                \Graphpinator\Parser\Exception\UnknownOperationType::class,
            ],
            [
                'query queryName field',
                \Graphpinator\Parser\Exception\ExpectedSelectionSet::class,
                'Expected selection set, got "name".',
            ],
            [
                'query queryName [$var: Int] { f }',
                \Graphpinator\Parser\Exception\ExpectedSelectionSet::class,
                'Expected selection set, got "[".',
            ],
            [
                'query queryName ($var: Int) field',
                \Graphpinator\Parser\Exception\ExpectedSelectionSet::class,
                'Expected selection set, got "name".',
            ],
            [
                'query queryName { ... {} }',
                \Graphpinator\Parser\Exception\ExpectedFragmentSpreadInfo::class,
                'Expected fragment name or inline fragment, got "{".',
            ],
            [
                'query queryName { ... on { f } }',
                \Graphpinator\Parser\Exception\ExpectedNamedType::class,
                'Expected named type without type modifiers, got "{".',
            ],
            [
                'query queryName { ... on Int! { f } }',
                \Graphpinator\Parser\Exception\ExpectedSelectionSet::class,
                'Expected selection set, got "!".',
            ],
            [
                'query queryName { ... on [Int] { f } }',
                \Graphpinator\Parser\Exception\ExpectedNamedType::class,
                'Expected named type without type modifiers, got "[".',
            ],
            [
                'query queryName { ... on [Int { f } }',
                \Graphpinator\Parser\Exception\ExpectedNamedType::class,
                'Expected named type without type modifiers, got "[".',
            ],
            [
                'query queryName { ... on Int }',
                \Graphpinator\Parser\Exception\ExpectedSelectionSet::class,
                'Expected selection set, got "}".',
            ],
            [
                'query queryName { ... @directive() }',
                \Graphpinator\Parser\Exception\ExpectedSelectionSet::class,
                'Expected selection set, got "}".',
            ],
            [
                'query queryName ($var: Int = @dir) { f }',
                \Graphpinator\Parser\Exception\ExpectedValue::class,
                'Expected value - either literal or variable reference, got ")".',
            ],
            [
                'query queryName ($var: Int = $var2) { f }',
                \Graphpinator\Parser\Exception\ExpectedLiteralValue::class,
                'Expected literal value as variable default value, got "$".',
            ],
            [
                'query queryName ($var = 123) { f }',
                \Graphpinator\Parser\Exception\ExpectedColon::class,
                'Expected colon, got "=".',
            ],
            [
                'query queryName { fieldName(arg = 123) }',
                \Graphpinator\Parser\Exception\ExpectedColon::class,
                'Expected colon, got "=".',
            ],
            [
                'query queryName { fieldName(arg: {123}}) }',
                \Graphpinator\Parser\Exception\ExpectedFieldName::class,
                'Expected field name, got "int literal".',
            ],
            [
                'query queryName { fieldName : { field } }',
                \Graphpinator\Parser\Exception\ExpectedFieldName::class,
                'Expected field name, got "{".',
            ],
            [
                'query queryName ($var: = 123) { f }',
                \Graphpinator\Parser\Exception\ExpectedType::class,
                'Expected type reference, got "=".',
            ],
            [
                'query queryName (Int = 5) { f }',
                \Graphpinator\Parser\Exception\ExpectedVariableName::class,
                'Expected variable or closing parenthesis, got "name".',
            ],
            [
                'query queryName (:Int = 5) { f }',
                \Graphpinator\Parser\Exception\ExpectedVariableName::class,
                'Expected variable or closing parenthesis, got ":".',
            ],
            [
                'query queryName { $var }',
                \Graphpinator\Parser\Exception\ExpectedSelectionSetBody::class,
                'Expected field name or fragment, got "$".',
            ],
            [
                'query queryName { fieldName(123) }',
                \Graphpinator\Parser\Exception\ExpectedArgumentName::class,
                'Expected argument or closing parenthesis, got "int literal".',
            ],
            [
                'query queryName { fieldName(: 123) }',
                \Graphpinator\Parser\Exception\ExpectedArgumentName::class,
                'Expected argument or closing parenthesis, got ":".',
            ],
            [
                'query queryName { field } query queryName { field }',
                \Graphpinator\Parser\Exception\DuplicateOperation::class,
                'Operation with this name already exists in current request.',
            ],
            [
                'query queryName { fieldName } { fieldName }',
                \Graphpinator\Parser\Exception\OperationWithoutName::class,
            ],
            [
                'query queryName { fieldName } query { fieldName }',
                \Graphpinator\Parser\Exception\OperationWithoutName::class,
            ],
            [
                'querry queryName { fieldName }',
                \Graphpinator\Parser\Exception\UnknownOperationType::class,
            ],
            [
                'query queryName { fieldName(duplicateArgument: 123, duplicateArgument: 123) }',
                \Graphpinator\Parser\Exception\DuplicateArgument::class,
                'Argument with name "duplicateArgument" already exists on current field.',
            ],
            [
                'fragment on on TypeName { field }',
                \Graphpinator\Parser\Exception\DisallowedFragmentName::class,
                'Invalid fragment name. Fragment cannot be named "on".',
            ],
            [
                'fragment fragmentName TypeName { field }',
                \Graphpinator\Parser\Exception\ExpectedTypeCondition::class,
                'Expected type condition for fragment, got "name".',
            ],
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     * @param string $input
     * @param string $exception
     * @param string|null $message
     */
    public function testInvalid(string $input, string $exception, ?string $message = null) : void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($message ?: \constant($exception . '::MESSAGE'));

        \Graphpinator\Parser\Parser::parseString($input);
    }
}
