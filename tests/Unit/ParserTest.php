<?php

declare(strict_types = 1);

namespace Graphpinator\Parser\Tests\Unit;

use Graphpinator\Parser\Exception\DisallowedFragmentName;
use Graphpinator\Parser\Exception\DuplicateArgument;
use Graphpinator\Parser\Exception\DuplicateOperation;
use Graphpinator\Parser\Exception\EmptyRequest;
use Graphpinator\Parser\Exception\ExpectedArgumentName;
use Graphpinator\Parser\Exception\ExpectedColon;
use Graphpinator\Parser\Exception\ExpectedFieldName;
use Graphpinator\Parser\Exception\ExpectedFragmentSpreadInfo;
use Graphpinator\Parser\Exception\ExpectedLiteralValue;
use Graphpinator\Parser\Exception\ExpectedNamedType;
use Graphpinator\Parser\Exception\ExpectedRoot;
use Graphpinator\Parser\Exception\ExpectedSelectionSet;
use Graphpinator\Parser\Exception\ExpectedSelectionSetBody;
use Graphpinator\Parser\Exception\ExpectedType;
use Graphpinator\Parser\Exception\ExpectedTypeCondition;
use Graphpinator\Parser\Exception\ExpectedValue;
use Graphpinator\Parser\Exception\ExpectedVariableName;
use Graphpinator\Parser\Exception\MissingOperation;
use Graphpinator\Parser\Exception\OperationWithoutName;
use Graphpinator\Parser\Exception\UnexpectedEnd;
use Graphpinator\Parser\Exception\UnknownOperationType;
use Graphpinator\Parser\Field\FieldSet;
use Graphpinator\Parser\FragmentSpread\InlineFragmentSpread;
use Graphpinator\Parser\FragmentSpread\NamedFragmentSpread;
use Graphpinator\Parser\OperationType;
use Graphpinator\Parser\Parser;
use Graphpinator\Parser\TypeRef\ListTypeRef;
use Graphpinator\Parser\TypeRef\NamedTypeRef;
use Graphpinator\Parser\TypeRef\NotNullRef;
use Graphpinator\Parser\Value\ArgumentValueSet;
use Graphpinator\Parser\Value\VariableRef;
use Graphpinator\Source\StringSource;
use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    public function testConstructor() : void
    {
        $source = new StringSource('query queryName { field }');
        $parser = new Parser();
        $result = $parser->parse($source);

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);
        self::assertCount(0, $result->operations->current()->variables);
        self::assertCount(1, $result->operations->current()->children);
        self::assertSame(OperationType::QUERY, $result->operations->current()->type);
        self::assertSame('queryName', $result->operations->current()->name);
    }

    public function testQuery() : void
    {
        $result = Parser::parseString('query queryName { field }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);
        self::assertCount(0, $result->operations->current()->variables);
        self::assertCount(1, $result->operations->current()->children);
        self::assertSame(OperationType::QUERY, $result->operations->current()->type);
        self::assertSame('queryName', $result->operations->current()->name);
    }

    public function testMutation() : void
    {
        $result = Parser::parseString('mutation mutName { field }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);
        self::assertCount(0, $result->operations->current()->variables);
        self::assertCount(1, $result->operations->current()->children);
        self::assertSame(OperationType::MUTATION, $result->operations->current()->type);
        self::assertSame('mutName', $result->operations->current()->name);
    }

    public function testSubscription() : void
    {
        $result = Parser::parseString('subscription subName { field }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);
        self::assertCount(0, $result->operations->current()->variables);
        self::assertCount(1, $result->operations->current()->children);
        self::assertSame(OperationType::SUBSCRIPTION, $result->operations->current()->type);
        self::assertSame('subName', $result->operations->current()->name);
    }

    public function testQueryNoName() : void
    {
        $result = Parser::parseString('query { field }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);
        self::assertCount(0, $result->operations->current()->variables);
        self::assertCount(1, $result->operations->current()->children);
        self::assertSame(OperationType::QUERY, $result->operations->current()->type);
        self::assertNull($result->operations->current()->name);
    }

    public function testQueryShorthand() : void
    {
        $result = Parser::parseString('{ field }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);
        self::assertCount(0, $result->operations->current()->variables);
        self::assertCount(1, $result->operations->current()->children);
        self::assertSame(OperationType::QUERY, $result->operations->current()->type);
        self::assertNull($result->operations->current()->name);
    }

    public function testQueryMultiple() : void
    {
        $result = Parser::parseString('query qName { field } mutation mName { field }');

        self::assertCount(0, $result->fragments);
    }

    public function testDirective() : void
    {
        $result = Parser::parseString('query { field @directiveName(arg1: 123) }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(1, $operation->children);
        self::assertArrayHasKey(0, $operation->children);
        self::assertCount(1, $operation->children->offsetGet(0)->directives);
        self::assertArrayHasKey(0, $operation->children->offsetGet(0)->directives);
        self::assertSame('directiveName', $operation->children->offsetGet(0)->directives->offsetGet(0)->name);
        self::assertCount(1, $operation->children->offsetGet(0)->directives->offsetGet(0)->arguments);
        self::assertArrayHasKey('arg1', $operation->children->offsetGet(0)->directives->offsetGet(0)->arguments);
        self::assertSame(
            'arg1',
            $operation
                ->children
                ->offsetGet(0)
                ->directives
                ->offsetGet(0)
                ->arguments
                ->offsetGet('arg1')
                ->name,
        );
        self::assertSame(
            123,
            $operation
                ->children
                ->offsetGet(0)
                ->directives
                ->offsetGet(0)
                ->arguments
                ->offsetGet('arg1')
                ->value
                ->getRawValue(),
        );
    }

    public function testFragment() : void
    {
        $result = Parser::parseString('fragment fragmentName on TypeName { field } query queryName { field }');

        self::assertCount(1, $result->fragments);
        self::assertCount(1, $result->operations);
        self::assertArrayHasKey('fragmentName', $result->fragments);
        self::assertSame('fragmentName', $result->fragments->offsetGet('fragmentName')->name);
        self::assertSame('TypeName', $result->fragments->offsetGet('fragmentName')->typeCond->name);
        self::assertCount(1, $result->fragments->offsetGet('fragmentName')->fields);
        self::assertCount(1, $result->fragments->offsetGet('fragmentName')->fields);
        self::assertCount(0, $result->operations->current()->variables);
        self::assertCount(1, $result->operations->current()->children);
        self::assertSame(OperationType::QUERY, $result->operations->current()->type);
        self::assertSame('queryName', $result->operations->current()->name);
        self::assertCount(0, $result->fragments->offsetGet('fragmentName')->directives);
    }

    public function testFragmentDirectives() : void
    {
        $result = Parser::parseString('fragment fragmentName on TypeName @abc { field } query { field }');

        self::assertCount(1, $result->fragments);
        self::assertCount(1, $result->operations);
        self::assertArrayHasKey('fragmentName', $result->fragments);
        self::assertSame('fragmentName', $result->fragments->offsetGet('fragmentName')->name);
        self::assertSame('TypeName', $result->fragments->offsetGet('fragmentName')->typeCond->name);
        self::assertCount(1, $result->fragments->offsetGet('fragmentName')->fields);
        self::assertCount(1, $result->fragments->offsetGet('fragmentName')->fields);
        self::assertCount(0, $result->operations->current()->variables);
        self::assertCount(1, $result->operations->current()->children);
        self::assertSame(OperationType::QUERY, $result->operations->current()->type);
        self::assertNull($result->operations->current()->name);
        self::assertCount(1, $result->fragments->offsetGet('fragmentName')->directives);
        self::assertSame('abc', $result->fragments->offsetGet('fragmentName')->directives->offsetGet(0)->name);
    }

    public function testNamedFragmentSpread() : void
    {
        $result = Parser::parseString('query { ... fragmentName } ');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(0, $operation->children);
        self::assertCount(1, $operation->children->getFragmentSpreads());
        self::assertArrayHasKey(0, $operation->children->getFragmentSpreads());
        self::assertInstanceOf(
            NamedFragmentSpread::class,
            $operation->children->getFragmentSpreads()[0],
        );
        self::assertSame('fragmentName', $operation->children->getFragmentSpreads()->offsetGet(0)->name);
        self::assertCount(0, $operation->children->getFragmentSpreads()->offsetGet(0)->directives);
    }

    public function testInlineFragmentSpread() : void
    {
        $result = Parser::parseString('query { ... on TypeName { fieldName } }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(0, $operation->children);
        self::assertCount(1, $operation->children->getFragmentSpreads());
        self::assertArrayHasKey(0, $operation->children->getFragmentSpreads());
        self::assertInstanceOf(
            InlineFragmentSpread::class,
            $operation->children->getFragmentSpreads()[0],
        );
        self::assertSame('TypeName', $operation->children->getFragmentSpreads()->offsetGet(0)->typeCond->name);
        self::assertCount(1, $operation->children->getFragmentSpreads()->offsetGet(0)->fields);
        self::assertCount(0, $operation->children->getFragmentSpreads()->offsetGet(0)->directives);
    }

    public function testNamedFragmentSpreadDirective() : void
    {
        $result = Parser::parseString('query { ... fragmentName @directiveName() }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(0, $operation->children);
        self::assertCount(1, $operation->children->getFragmentSpreads());
        self::assertArrayHasKey(0, $operation->children->getFragmentSpreads());
        self::assertInstanceOf(
            NamedFragmentSpread::class,
            $operation->children->getFragmentSpreads()[0],
        );
        self::assertSame('fragmentName', $operation->children->getFragmentSpreads()->offsetGet(0)->name);
        self::assertCount(1, $operation->children->getFragmentSpreads()->offsetGet(0)->directives);
        self::assertArrayHasKey(0, $operation->children->getFragmentSpreads()->offsetGet(0)->directives);
        self::assertSame(
            'directiveName',
            $operation->children->getFragmentSpreads()->offsetGet(0)->directives->offsetGet(0)->name,
        );
    }

    public function testInlineFragmentSpreadDirective() : void
    {
        $result = Parser::parseString('query { ... @directiveName() { fieldName } }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(0, $operation->children);
        self::assertCount(1, $operation->children->getFragmentSpreads());
        self::assertArrayHasKey(0, $operation->children->getFragmentSpreads());
        self::assertInstanceOf(
            InlineFragmentSpread::class,
            $operation->children->getFragmentSpreads()[0],
        );
        self::assertNull($operation->children->getFragmentSpreads()->offsetGet(0)->typeCond);
        self::assertCount(1, $operation->children->getFragmentSpreads()->offsetGet(0)->fields);
        self::assertCount(1, $operation->children->getFragmentSpreads()->offsetGet(0)->directives);
        self::assertArrayHasKey(0, $operation->children->getFragmentSpreads()->offsetGet(0)->directives);
        self::assertSame(
            'directiveName',
            $operation->children->getFragmentSpreads()->offsetGet(0)->directives->offsetGet(0)->name,
        );
    }

    public function testInlineFragmentSpreadBoth() : void
    {
        $result = Parser::parseString('query { ... on TypeName @directiveName() { fieldName } }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(0, $operation->children);
        self::assertCount(1, $operation->children->getFragmentSpreads());
        self::assertArrayHasKey(0, $operation->children->getFragmentSpreads());
        self::assertInstanceOf(
            InlineFragmentSpread::class,
            $operation->children->getFragmentSpreads()[0],
        );
        self::assertSame('TypeName', $operation->children->getFragmentSpreads()->offsetGet(0)->typeCond->name);
        self::assertCount(1, $operation->children->getFragmentSpreads()->offsetGet(0)->fields);
        self::assertCount(1, $operation->children->getFragmentSpreads()->offsetGet(0)->directives);
        self::assertArrayHasKey(0, $operation->children->getFragmentSpreads()->offsetGet(0)->directives);
        self::assertSame(
            'directiveName',
            $operation->children->getFragmentSpreads()->offsetGet(0)->directives->offsetGet(0)->name,
        );
    }

    public function testVariable() : void
    {
        $result = Parser::parseString('query queryName ($varName: Int) { field }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertSame('queryName', $operation->name);
        self::assertCount(1, $operation->children);
        self::assertCount(1, $operation->variables);
        self::assertCount(0, $operation->directives);
        self::assertArrayHasKey('varName', $operation->variables);
        self::assertSame('varName', $operation->variables->offsetGet('varName')->name);
        self::assertInstanceOf(
            NamedTypeRef::class,
            $operation->variables->offsetGet('varName')->type,
        );
        self::assertSame('Int', $operation->variables->offsetGet('varName')->type->name);
        self::assertNull($operation->variables->offsetGet('varName')->default);
    }

    public function testVariableNoName() : void
    {
        $result = Parser::parseString('query ($varName: Int) { field }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertNull($operation->name);
        self::assertCount(1, $operation->children);
        self::assertCount(1, $operation->variables);
        self::assertCount(0, $operation->directives);
        self::assertArrayHasKey('varName', $operation->variables);
        self::assertSame('varName', $operation->variables->offsetGet('varName')->name);
        self::assertInstanceOf(
            NamedTypeRef::class,
            $operation->variables->offsetGet('varName')->type,
        );
        self::assertSame('Int', $operation->variables->offsetGet('varName')->type->name);
        self::assertNull($operation->variables->offsetGet('varName')->default);
    }

    public function testDirectiveNoName() : void
    {
        $result = Parser::parseString('query @directive { field }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertNull($operation->name);
        self::assertCount(1, $operation->children);
        self::assertCount(0, $operation->variables);
        self::assertCount(1, $operation->directives);
        self::assertArrayHasKey(0, $operation->directives);
        self::assertSame('directive', $operation->directives->offsetGet(0)->name);
        self::assertNull($operation->directives->offsetGet(0)->arguments);
    }

    public function testVariableDefault() : void
    {
        $result = Parser::parseString('query queryName ($varName: Float = 3.14) { field }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(1, $operation->children);
        self::assertCount(1, $operation->variables);
        self::assertArrayHasKey('varName', $operation->variables);
        self::assertSame('varName', $operation->variables->offsetGet('varName')->name);
        self::assertInstanceOf(
            NamedTypeRef::class,
            $operation->variables->offsetGet('varName')->type,
        );
        self::assertSame('Float', $operation->variables->offsetGet('varName')->type->name);
        self::assertSame(3.14, $operation->variables->offsetGet('varName')->default->getRawValue());
    }

    public function testVariableComplexType() : void
    {
        $result = Parser::parseString('query queryName ($varName: [Int!]!) { field }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(1, $operation->children);
        self::assertCount(1, $operation->variables);
        self::assertArrayHasKey('varName', $operation->variables);
        self::assertSame('varName', $operation->variables->offsetGet('varName')->name);
        self::assertInstanceOf(
            NotNullRef::class,
            $operation->variables->offsetGet('varName')->type,
        );
        self::assertInstanceOf(
            ListTypeRef::class,
            $operation->variables->offsetGet('varName')->type->innerRef,
        );
        self::assertInstanceOf(
            NotNullRef::class,
            $operation->variables->offsetGet('varName')->type->innerRef->innerRef,
        );
        self::assertInstanceOf(
            NamedTypeRef::class,
            $operation->variables->offsetGet('varName')->type->innerRef->innerRef->innerRef,
        );
        self::assertSame(
            'Int',
            $operation->variables->offsetGet('varName')->type->innerRef->innerRef->innerRef->name,
        );
    }

    public function testVariableMultiple() : void
    {
        $result = Parser::parseString('query queryName ($varName: Boolean = true, $varName2: Boolean!) { field }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(1, $operation->children);
        self::assertCount(2, $operation->variables);
        self::assertArrayHasKey('varName', $operation->variables);
        self::assertArrayHasKey('varName2', $operation->variables);
        self::assertSame('varName', $operation->variables->offsetGet('varName')->name);
        self::assertSame('varName2', $operation->variables->offsetGet('varName2')->name);
        self::assertInstanceOf(
            NamedTypeRef::class,
            $operation->variables->offsetGet('varName')->type,
        );
        self::assertSame('Boolean', $operation->variables->offsetGet('varName')->type->name);
        self::assertTrue($operation->variables->offsetGet('varName')->default->getRawValue());
        self::assertInstanceOf(
            NotNullRef::class,
            $operation->variables->offsetGet('varName2')->type,
        );
        self::assertInstanceOf(
            NamedTypeRef::class,
            $operation->variables->offsetGet('varName2')->type->innerRef,
        );
        self::assertSame('Boolean', $operation->variables->offsetGet('varName2')->type->innerRef->name);
        self::assertNull($operation->variables->offsetGet('varName2')->default);
    }

    public function testVariableDefaultList() : void
    {
        $result = Parser::parseString('query queryName ($varName: [Bool] = [true, false]) { field }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(1, $operation->children);
        self::assertCount(1, $operation->variables);
        self::assertArrayHasKey('varName', $operation->variables);
        self::assertSame('varName', $operation->variables->offsetGet('varName')->name);
        self::assertInstanceOf(
            ListTypeRef::class,
            $operation->variables->offsetGet('varName')->type,
        );
        self::assertInstanceOf(
            NamedTypeRef::class,
            $operation->variables->offsetGet('varName')->type->innerRef,
        );
        self::assertSame('Bool', $operation->variables->offsetGet('varName')->type->innerRef->name);
        self::assertSame([true, false], $operation->variables->offsetGet('varName')->default->getRawValue());
        self::assertCount(0, $operation->variables->offsetGet('varName')->directives);
    }

    public function testVariableDefaultObject() : void
    {
        $result = Parser::parseString('query queryName ($varName: InputType = {fieldName: null, fieldName2: {}}) { field }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(1, $operation->children);
        self::assertCount(1, $operation->variables);
        self::assertArrayHasKey('varName', $operation->variables);
        self::assertSame('varName', $operation->variables->offsetGet('varName')->name);
        self::assertInstanceOf(
            NamedTypeRef::class,
            $operation->variables->offsetGet('varName')->type,
        );
        self::assertSame('InputType', $operation->variables->offsetGet('varName')->type->name);
        self::assertEquals(
            (object) [
                'fieldName' => null,
                'fieldName2' => (object) [],
            ],
            $operation->variables->offsetGet('varName')->default->getRawValue(),
        );
        self::assertCount(0, $operation->variables->offsetGet('varName')->directives);
    }

    public function testVariableDefaultEnum() : void
    {
        $result = Parser::parseString('query queryName ($varName: EnumType = ENUM_VALUE) { field }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(1, $operation->children);
        self::assertCount(1, $operation->variables);
        self::assertArrayHasKey('varName', $operation->variables);
        self::assertSame('varName', $operation->variables->offsetGet('varName')->name);
        self::assertInstanceOf(
            NamedTypeRef::class,
            $operation->variables->offsetGet('varName')->type,
        );
        self::assertSame('EnumType', $operation->variables->offsetGet('varName')->type->name);
        self::assertEquals(
            'ENUM_VALUE',
            $operation->variables->offsetGet('varName')->default->getRawValue(),
        );
        self::assertCount(0, $operation->variables->offsetGet('varName')->directives);
    }

    public function testVariableDirectives() : void
    {
        $result = Parser::parseString('query queryName ($varName: Int @abc) { field }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(1, $operation->children);
        self::assertCount(1, $operation->variables);
        self::assertArrayHasKey('varName', $operation->variables);
        self::assertSame('varName', $operation->variables->offsetGet('varName')->name);
        self::assertInstanceOf(
            NamedTypeRef::class,
            $operation->variables->offsetGet('varName')->type,
        );
        self::assertSame('Int', $operation->variables->offsetGet('varName')->type->name);
        self::assertCount(1, $operation->variables->offsetGet('varName')->directives);
        self::assertSame('abc', $operation->variables->offsetGet('varName')->directives->offsetGet(0)->name);
    }

    public function testVariableUsage() : void
    {
        $result = Parser::parseString('query queryName ($varName: Int) { field(arg: $varName) }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertSame('queryName', $operation->name);
        self::assertCount(1, $operation->children);
        self::assertSame('field', $operation->children->offsetGet(0)->name);
        self::assertCount(1, $operation->children->offsetGet(0)->arguments);
        self::assertSame('arg', $operation->children->offsetGet(0)->arguments->offsetGet('arg')->name);
        self::assertInstanceOf(
            VariableRef::class,
            $operation->children->offsetGet(0)->arguments->offsetGet('arg')->value,
        );
        self::assertSame('varName', $operation->children->offsetGet(0)->arguments->offsetGet('arg')->value->varName);
        self::assertCount(1, $operation->variables);
        self::assertCount(0, $operation->directives);
        self::assertArrayHasKey('varName', $operation->variables);
        self::assertSame('varName', $operation->variables->offsetGet('varName')->name);
        self::assertInstanceOf(
            NamedTypeRef::class,
            $operation->variables->offsetGet('varName')->type,
        );
        self::assertSame('Int', $operation->variables->offsetGet('varName')->type->name);
        self::assertNull($operation->variables->offsetGet('varName')->default);
    }

    public function testField() : void
    {
        $result = Parser::parseString('query queryName { fieldName }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(1, $operation->children);
        self::assertCount(0, $operation->variables);
        self::assertArrayHasKey(0, $operation->children);
        self::assertSame('fieldName', $operation->children->offsetGet(0)->name);
        self::assertNull($operation->children->offsetGet(0)->alias);
        self::assertNull($operation->children->offsetGet(0)->arguments);
        self::assertNull($operation->children->offsetGet(0)->children);
    }

    public function testFieldArguments() : void
    {
        $result = Parser::parseString('query queryName { fieldName(argName: "argVal") }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(0, $operation->variables);
        self::assertCount(1, $operation->children);
        self::assertArrayHasKey(0, $operation->children);
        self::assertSame('fieldName', $operation->children->offsetGet(0)->name);
        self::assertNull($operation->children->offsetGet(0)->alias);
        self::assertInstanceOf(
            ArgumentValueSet::class,
            $operation->children->offsetGet(0)->arguments,
        );
        self::assertCount(1, $operation->children->offsetGet(0)->arguments);
        self::assertArrayHasKey('argName', $operation->children->offsetGet(0)->arguments);
        self::assertSame('argVal', $operation->children->offsetGet(0)->arguments->offsetGet('argName')->value->getRawValue());
        self::assertNull($operation->children->offsetGet(0)->children);
    }

    public function testFieldArgumentsKeywordName() : void
    {
        $result = Parser::parseString('query queryName { type(input: "argVal") }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(0, $operation->variables);
        self::assertCount(1, $operation->children);
        self::assertArrayHasKey(0, $operation->children);
        self::assertSame('type', $operation->children->offsetGet(0)->name);
        self::assertNull($operation->children->offsetGet(0)->alias);
        self::assertInstanceOf(
            ArgumentValueSet::class,
            $operation->children->offsetGet(0)->arguments,
        );
        self::assertCount(1, $operation->children->offsetGet(0)->arguments);
        self::assertArrayHasKey('input', $operation->children->offsetGet(0)->arguments);
        self::assertSame('argVal', $operation->children->offsetGet(0)->arguments->offsetGet('input')->value->getRawValue());
        self::assertNull($operation->children->offsetGet(0)->children);
    }

    public function testFieldSubfield() : void
    {
        $result = Parser::parseString('query queryName { fieldName { innerField } }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(0, $operation->variables);
        self::assertCount(1, $operation->children);
        self::assertArrayHasKey(0, $operation->children);
        self::assertSame('fieldName', $operation->children->offsetGet(0)->name);
        self::assertNull($operation->children->offsetGet(0)->alias);
        self::assertNull($operation->children->offsetGet(0)->arguments);
        self::assertInstanceOf(FieldSet::class, $operation->children->offsetGet(0)->children);
        self::assertCount(1, $operation->children->offsetGet(0)->children);
        self::assertArrayHasKey(0, $operation->children->offsetGet(0)->children);
        self::assertSame('innerField', $operation->children->offsetGet(0)->children->offsetGet(0)->name);
        self::assertNull($operation->children->offsetGet(0)->children->offsetGet(0)->alias);
        self::assertNull($operation->children->offsetGet(0)->children->offsetGet(0)->arguments);
    }

    public function testFieldAlias() : void
    {
        $result = Parser::parseString('query queryName { aliasName: fieldName }');

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(1, $operation->children);
        self::assertCount(0, $operation->variables);
        self::assertArrayHasKey(0, $operation->children);
        self::assertSame('fieldName', $operation->children->offsetGet(0)->name);
        self::assertSame('aliasName', $operation->children->offsetGet(0)->alias);
        self::assertNull($operation->children->offsetGet(0)->arguments);
        self::assertNull($operation->children->offsetGet(0)->children);
    }

    public function testFieldAll() : void
    {
        $result = Parser::parseString(
            'query queryName { aliasName: fieldName(argName: "argVal") { innerField(argName: 12.34) }}',
        );

        self::assertCount(0, $result->fragments);
        self::assertCount(1, $result->operations);

        $operation = $result->operations->current();

        self::assertCount(0, $operation->variables);
        self::assertCount(1, $operation->children);
        self::assertArrayHasKey(0, $operation->children);
        self::assertSame('fieldName', $operation->children->offsetGet(0)->name);
        self::assertSame('aliasName', $operation->children->offsetGet(0)->alias);
        self::assertInstanceOf(
            ArgumentValueSet::class,
            $operation->children->offsetGet(0)->arguments,
        );
        self::assertCount(1, $operation->children->offsetGet(0)->arguments);
        self::assertArrayHasKey('argName', $operation->children->offsetGet(0)->arguments);
        self::assertInstanceOf(FieldSet::class, $operation->children->offsetGet(0)->children);
        self::assertCount(1, $operation->children->offsetGet(0)->children);
        self::assertArrayHasKey(0, $operation->children->offsetGet(0)->children);
        self::assertSame('innerField', $operation->children->offsetGet(0)->children->offsetGet(0)->name);
        self::assertNull($operation->children->offsetGet(0)->children->offsetGet(0)->alias);
        self::assertInstanceOf(
            ArgumentValueSet::class,
            $operation->children->offsetGet(0)->children->offsetGet(0)->arguments,
        );
        self::assertCount(1, $operation->children->offsetGet(0)->children->offsetGet(0)->arguments);
    }

    public function testMultipleOperations() : void
    {
        $result = Parser::parseString('
            query queryName { aliasName: fieldName(argName: "argVal") { innerField(argName: 12.34) }}
            query anotherQuery { fieldName(argName: "argVal2") { innerField(argName: 12.35) }}
            query lastQuery { fieldName(argName: "argVal3") { innerField(argName: 12.36) }}
        ');

        self::assertCount(0, $result->fragments);
        self::assertCount(3, $result->operations);

        $operation = $result->operations['queryName'];

        self::assertCount(0, $operation->variables);
        self::assertCount(1, $operation->children);
        self::assertArrayHasKey(0, $operation->children);
        self::assertSame('fieldName', $operation->children->offsetGet(0)->name);
        self::assertSame('aliasName', $operation->children->offsetGet(0)->alias);
        self::assertInstanceOf(
            ArgumentValueSet::class,
            $operation->children->offsetGet(0)->arguments,
        );
        self::assertCount(1, $operation->children->offsetGet(0)->arguments);
        self::assertArrayHasKey('argName', $operation->children->offsetGet(0)->arguments);
        self::assertInstanceOf(FieldSet::class, $operation->children->offsetGet(0)->children);
        self::assertCount(1, $operation->children->offsetGet(0)->children);
        self::assertArrayHasKey(0, $operation->children->offsetGet(0)->children);
        self::assertSame('innerField', $operation->children->offsetGet(0)->children->offsetGet(0)->name);
        self::assertNull($operation->children->offsetGet(0)->children->offsetGet(0)->alias);
        self::assertInstanceOf(
            ArgumentValueSet::class,
            $operation->children->offsetGet(0)->children->offsetGet(0)->arguments,
        );
        self::assertCount(1, $operation->children->offsetGet(0)->children->offsetGet(0)->arguments);
    }

    public static function invalidDataProvider() : array
    {
        return [
            ['', EmptyRequest::class],
            ['query', UnexpectedEnd::class],
            [
                '{}',
                ExpectedSelectionSetBody::class,
                'Expected field name or fragment, got "}".',
            ],
            [
                '{   }',
                ExpectedSelectionSetBody::class,
                'Expected field name or fragment, got "}".',
            ],
            [
                'query {}',
                ExpectedSelectionSetBody::class,
                'Expected field name or fragment, got "}".',
            ],
            [
                '$var',
                ExpectedRoot::class,
                'Expected operation or fragment definition, got "$".',
            ],
            [
                'fragment fragmentName on TypeName { f }',
                MissingOperation::class,
            ],
            [
                'fragment fragmentName on TypeName! { f }',
                ExpectedSelectionSet::class,
                'Expected selection set, got "!".',
            ],
            [
                'fragment fragmentName on [TypeName] { f }',
                ExpectedNamedType::class,
                'Expected named type without type modifiers, got "[".',
            ],
            [
                'fragment fragmentName { f }',
                ExpectedTypeCondition::class,
                'Expected type condition for fragment, got "{".',
            ],
            [
                'fragment fragmentName on { f }',
                ExpectedNamedType::class,
                'Expected named type without type modifiers, got "{".',
            ],
            [
                'queryName { f }',
                UnknownOperationType::class,
            ],
            [
                'queary queryName { f }',
                UnknownOperationType::class,
            ],
            [
                'query queryName field',
                ExpectedSelectionSet::class,
                'Expected selection set, got "name".',
            ],
            [
                'query queryName [$var: Int] { f }',
                ExpectedSelectionSet::class,
                'Expected selection set, got "[".',
            ],
            [
                'query queryName ($var: Int) field',
                ExpectedSelectionSet::class,
                'Expected selection set, got "name".',
            ],
            [
                'query queryName { ... {} }',
                ExpectedFragmentSpreadInfo::class,
                'Expected fragment name or inline fragment, got "{".',
            ],
            [
                'query queryName { ... on { f } }',
                ExpectedNamedType::class,
                'Expected named type without type modifiers, got "{".',
            ],
            [
                'query queryName { ... on Int! { f } }',
                ExpectedSelectionSet::class,
                'Expected selection set, got "!".',
            ],
            [
                'query queryName { ... on [Int] { f } }',
                ExpectedNamedType::class,
                'Expected named type without type modifiers, got "[".',
            ],
            [
                'query queryName { ... on [Int { f } }',
                ExpectedNamedType::class,
                'Expected named type without type modifiers, got "[".',
            ],
            [
                'query queryName { ... on Int }',
                ExpectedSelectionSet::class,
                'Expected selection set, got "}".',
            ],
            [
                'query queryName { ... @directive() }',
                ExpectedSelectionSet::class,
                'Expected selection set, got "}".',
            ],
            [
                'query queryName ($var: Int = @dir) { f }',
                ExpectedValue::class,
                'Expected value - either literal or variable reference, got ")".',
            ],
            [
                'query queryName ($var: Int = $var2) { f }',
                ExpectedLiteralValue::class,
                'Expected literal value as variable default value, got "$".',
            ],
            [
                'query queryName ($var = 123) { f }',
                ExpectedColon::class,
                'Expected colon, got "=".',
            ],
            [
                'query queryName { fieldName(arg = 123) }',
                ExpectedColon::class,
                'Expected colon, got "=".',
            ],
            [
                'query queryName { fieldName(arg: {123}}) }',
                ExpectedFieldName::class,
                'Expected field name, got "int literal".',
            ],
            [
                'query queryName { fieldName : { field } }',
                ExpectedFieldName::class,
                'Expected field name, got "{".',
            ],
            [
                'query queryName ($var: = 123) { f }',
                ExpectedType::class,
                'Expected type reference, got "=".',
            ],
            [
                'query queryName (Int = 5) { f }',
                ExpectedVariableName::class,
                'Expected variable or closing parenthesis, got "name".',
            ],
            [
                'query queryName (:Int = 5) { f }',
                ExpectedVariableName::class,
                'Expected variable or closing parenthesis, got ":".',
            ],
            [
                'query queryName { $var }',
                ExpectedSelectionSetBody::class,
                'Expected field name or fragment, got "$".',
            ],
            [
                'query queryName { fieldName(123) }',
                ExpectedArgumentName::class,
                'Expected argument or closing parenthesis, got "int literal".',
            ],
            [
                'query queryName { fieldName(: 123) }',
                ExpectedArgumentName::class,
                'Expected argument or closing parenthesis, got ":".',
            ],
            [
                'query queryName { field } query queryName { field }',
                DuplicateOperation::class,
                'Operation with this name already exists in current request.',
            ],
            [
                'query queryName { fieldName } { fieldName }',
                OperationWithoutName::class,
            ],
            [
                'query queryName { fieldName } query { fieldName }',
                OperationWithoutName::class,
            ],
            [
                'querry queryName { fieldName }',
                UnknownOperationType::class,
            ],
            [
                'query queryName { fieldName(duplicateArgument: 123, duplicateArgument: 123) }',
                DuplicateArgument::class,
                'Argument with name "duplicateArgument" already exists on current field.',
            ],
            [
                'fragment on on TypeName { field }',
                DisallowedFragmentName::class,
                'Invalid fragment name. Fragment cannot be named "on".',
            ],
            [
                'fragment fragmentName TypeName { field }',
                ExpectedTypeCondition::class,
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

        Parser::parseString($input);
    }
}
