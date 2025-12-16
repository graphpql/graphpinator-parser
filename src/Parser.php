<?php

declare(strict_types = 1);

namespace Graphpinator\Parser;

use Graphpinator\Common\Location;
use Graphpinator\Parser\Directive\Directive;
use Graphpinator\Parser\Directive\DirectiveSet;
use Graphpinator\Parser\Exception\DisallowedFragmentName;
use Graphpinator\Parser\Exception\DuplicateArgument;
use Graphpinator\Parser\Exception\DuplicateOperation;
use Graphpinator\Parser\Exception\EmptyRequest;
use Graphpinator\Parser\Exception\ExpectedArgumentName;
use Graphpinator\Parser\Exception\ExpectedClosingBracket;
use Graphpinator\Parser\Exception\ExpectedColon;
use Graphpinator\Parser\Exception\ExpectedFieldName;
use Graphpinator\Parser\Exception\ExpectedFragmentName;
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
use Graphpinator\Parser\Exception\UnknownOperationType;
use Graphpinator\Parser\Field\Field;
use Graphpinator\Parser\Field\FieldSet;
use Graphpinator\Parser\Fragment\Fragment;
use Graphpinator\Parser\Fragment\FragmentSet;
use Graphpinator\Parser\FragmentSpread\FragmentSpread;
use Graphpinator\Parser\FragmentSpread\FragmentSpreadSet;
use Graphpinator\Parser\FragmentSpread\InlineFragmentSpread;
use Graphpinator\Parser\FragmentSpread\NamedFragmentSpread;
use Graphpinator\Parser\Operation\Operation;
use Graphpinator\Parser\Operation\OperationSet;
use Graphpinator\Parser\TypeRef\ListTypeRef;
use Graphpinator\Parser\TypeRef\NamedTypeRef;
use Graphpinator\Parser\TypeRef\NotNullRef;
use Graphpinator\Parser\TypeRef\TypeRef;
use Graphpinator\Parser\Value\ArgumentValue;
use Graphpinator\Parser\Value\ArgumentValueSet;
use Graphpinator\Parser\Value\EnumLiteral;
use Graphpinator\Parser\Value\ListVal;
use Graphpinator\Parser\Value\Literal;
use Graphpinator\Parser\Value\ObjectVal;
use Graphpinator\Parser\Value\Value;
use Graphpinator\Parser\Value\VariableRef;
use Graphpinator\Parser\Variable\Variable;
use Graphpinator\Parser\Variable\VariableSet;
use Graphpinator\Source\Source;
use Graphpinator\Source\StringSource;
use Graphpinator\Tokenizer\TokenType;

final class Parser
{
    private TokenContainer $tokenizer;

    /**
     * Static shortcut.
     */
    public static function parseString(string $source) : ParsedRequest
    {
        return (new self())->parse(new StringSource($source));
    }

    /**
     * Parses document and produces ParseResult object.
     */
    public function parse(Source $source) : ParsedRequest
    {
        $this->tokenizer = new TokenContainer($source);

        if ($this->tokenizer->isEmpty()) {
            throw new EmptyRequest(new Location(1, 1));
        }

        $fragments = [];
        $locations = [];
        $operations = [];
        $operationNames = [];

        while (true) {
            $location = $this->tokenizer->getCurrent()->getLocation();

            switch ($this->tokenizer->getCurrent()->getType()) {
                // query shorthand
                case TokenType::CUR_O:
                    $operation = new Operation(
                        TokenType::QUERY->value,
                        null,
                        new VariableSet(),
                        new DirectiveSet(),
                        $this->parseSelectionSet(),
                    );

                    $operations[$operation->getName()] = $operation;
                    $locations[] = $location;
                    $operationNames[] = null;

                    break;
                case TokenType::NAME:
                    switch ($this->tokenizer->getCurrent()->getValue()) {
                        case TokenType::FRAGMENT->value:
                            $fragment = $this->parseFragmentDefinition();
                            $fragments[$fragment->getName()] = $fragment;

                            break;
                        case TokenType::QUERY->value:
                        case TokenType::MUTATION->value:
                        case TokenType::SUBSCRIPTION->value:
                            $operationType = $this->tokenizer->getCurrent()->getValue();
                            $this->tokenizer->getNext();

                            $operation = new Operation(
                                $operationType,
                                ...$this->parseAfterOperationType(),
                            );

                            if (\in_array($operation->getName(), $operationNames, true)) {
                                throw new DuplicateOperation($location);
                            }

                            $operations[$operation->getName()] = $operation;
                            $locations[] = $location;
                            $operationNames[] = $operation->getName();

                            break;
                        default:
                            throw new UnknownOperationType($this->tokenizer->getCurrent()->getLocation());
                    }

                    break;
                default:
                    throw new ExpectedRoot(
                        $this->tokenizer->getCurrent()->getLocation(),
                        $this->tokenizer->getCurrent()->getType(),
                    );
            }

            if (!$this->tokenizer->hasNext()) {
                break;
            }

            $this->tokenizer->getNext();
        }

        $parsedRequest = new ParsedRequest(
            new OperationSet($operations),
            new FragmentSet($fragments),
        );

        return match (\count($operations)) {
            0 => throw new MissingOperation($this->tokenizer->getCurrent()->getLocation()),
            1 => $parsedRequest,
            default => \in_array(null, $operationNames, true)
                ? throw new OperationWithoutName($locations[\array_search(null, $operationNames, true)])
                : $parsedRequest,
        };
    }

    /**
     * Parses fragment definition after fragment keyword.
     *
     * Expects iterator on previous token - fragment keyword
     * Leaves iterator to last used token - closing brace
     */
    private function parseFragmentDefinition() : Fragment
    {
        $fragmentName = $this->tokenizer->assertNext(TokenType::NAME, ExpectedFragmentName::class)->getValue();

        if ($fragmentName === TokenType::ON->value) {
            throw new DisallowedFragmentName($this->tokenizer->getPrev()->getLocation());
        }

        $this->tokenizer->assertNextValue(TokenType::NAME, TokenType::ON->value, ExpectedTypeCondition::class);
        $typeCond = $this->parseNamedType();
        $directives = $this->parseDirectives();
        $this->tokenizer->assertNext(TokenType::CUR_O, ExpectedSelectionSet::class);

        return new Fragment(
            $fragmentName,
            $typeCond,
            $directives,
            $this->parseSelectionSet(),
        );
    }

    private function parseAfterOperationType() : array
    {
        $operationName = null;

        if ($this->tokenizer->getCurrent()->getType() === TokenType::NAME) {
            $operationName = $this->tokenizer->getCurrent()->getValue();
            $this->tokenizer->getNext();
        }

        return [
            $operationName,
            ...$this->parseAfterOperationName(),
        ];
    }

    private function parseAfterOperationName() : array
    {
        $variables = null;

        if ($this->tokenizer->getCurrent()->getType() === TokenType::PAR_O) {
            $variables = $this->parseVariables();
            $this->tokenizer->getNext();
        }

        return [
            $variables,
            ...$this->parseAfterOperationVariables(),
        ];
    }

    private function parseAfterOperationVariables() : array
    {
        $directives = null;

        if ($this->tokenizer->getCurrent()->getType() === TokenType::DIRECTIVE) {
            $this->tokenizer->getPrev();
            $directives = $this->parseDirectives();
            $this->tokenizer->getNext();
        }

        if ($this->tokenizer->getCurrent()->getType() === TokenType::CUR_O) {
            return [
                $directives,
                $this->parseSelectionSet(),
            ];
        }

        throw new ExpectedSelectionSet(
            $this->tokenizer->getCurrent()->getLocation(),
            $this->tokenizer->getCurrent()->getType(),
        );
    }

    /**
     * Parses selection set.
     *
     * Expects iterator on previous token - opening brace
     * Leaves iterator to last used token - closing brace
     */
    private function parseSelectionSet() : FieldSet
    {
        $fields = [];
        $fragments = [];

        do {
            switch ($this->tokenizer->peekNext()->getType()) {
                case TokenType::ELLIP:
                    $this->tokenizer->getNext();
                    $fragments[] = $this->parseFragmentSpread();

                    break;
                case TokenType::NAME:
                    $this->tokenizer->getNext();
                    $fields[] = $this->parseField();

                    break;
                default:
                    throw new ExpectedSelectionSetBody(
                        $this->tokenizer->getNext()->getLocation(),
                        $this->tokenizer->getCurrent()->getType(),
                    );
            }
        } while ($this->tokenizer->peekNext()->getType() !== TokenType::CUR_C);

        $this->tokenizer->getNext();

        return new FieldSet($fields, new FragmentSpreadSet($fragments));
    }

    /**
     * Parses single field.
     *
     * Expects iterator on previous token - field name
     * Leaves iterator to last used token - last token in field definition
     */
    private function parseField() : Field
    {
        $fieldName = $this->tokenizer->getCurrent()->getValue();
        $aliasName = null;
        $arguments = null;
        $children = null;

        if ($this->tokenizer->peekNext()->getType() === TokenType::COLON) {
            $this->tokenizer->getNext();

            $aliasName = $fieldName;
            $fieldName = $this->tokenizer->assertNext(TokenType::NAME, ExpectedFieldName::class)->getValue();
        }

        if ($this->tokenizer->peekNext()->getType() === TokenType::PAR_O) {
            $this->tokenizer->getNext();
            $arguments = $this->parseArguments();
        }

        $directives = $this->parseDirectives();

        if ($this->tokenizer->peekNext()->getType() === TokenType::CUR_O) {
            $this->tokenizer->getNext();
            $children = $this->parseSelectionSet();
        }

        return new Field($fieldName, $aliasName, $children, $arguments, $directives);
    }

    /**
     * Parses fragment spread after ellipsis.
     *
     * Expects iterator on previous token - ellipsis
     * Leaves iterator to last used token - either fragment name or closing brace
     */
    private function parseFragmentSpread() : FragmentSpread
    {
        switch ($this->tokenizer->getNext()->getType()) {
            case TokenType::NAME:
                if ($this->tokenizer->getCurrent()->getValue() === TokenType::ON->value) {
                    $typeCond = $this->parseNamedType();
                    $directives = $this->parseDirectives();
                    $this->tokenizer->assertNext(TokenType::CUR_O, ExpectedSelectionSet::class);

                    return new InlineFragmentSpread(
                        $this->parseSelectionSet(),
                        $directives,
                        $typeCond,
                    );
                }

                return new NamedFragmentSpread(
                    $this->tokenizer->getCurrent()->getValue(),
                    $this->parseDirectives(),
                );
            case TokenType::DIRECTIVE:
                $this->tokenizer->getPrev();
                $directives = $this->parseDirectives();
                $this->tokenizer->assertNext(TokenType::CUR_O, ExpectedSelectionSet::class);

                return new InlineFragmentSpread(
                    $this->parseSelectionSet(),
                    $directives,
                    null,
                );
            default:
                throw new ExpectedFragmentSpreadInfo(
                    $this->tokenizer->getCurrent()->getLocation(),
                    $this->tokenizer->getCurrent()->getType(),
                );
        }
    }

    /**
     * Parses variables definition.
     *
     * Expects iterator on previous token - opening parenthesis
     * Leaves iterator to last used token - closing parenthesis
     */
    private function parseVariables() : VariableSet
    {
        $variables = [];

        while ($this->tokenizer->peekNext()->getType() !== TokenType::PAR_C) {
            if ($this->tokenizer->getNext()->getType() !== TokenType::VARIABLE) {
                throw new ExpectedVariableName(
                    $this->tokenizer->getCurrent()->getLocation(),
                    $this->tokenizer->getCurrent()->getType(),
                );
            }

            $name = $this->tokenizer->getCurrent()->getValue();
            $this->tokenizer->assertNext(TokenType::COLON, ExpectedColon::class);
            $type = $this->parseType();
            $default = null;

            if ($this->tokenizer->peekNext()->getType() === TokenType::EQUAL) {
                $this->tokenizer->getNext();
                $default = $this->parseValue(true);
            }

            $variables[] = new Variable(
                $name,
                $type,
                $default,
                $this->parseDirectives(),
            );
        }

        $this->tokenizer->getNext();

        return new VariableSet($variables);
    }

    /**
     * Parses directive list.
     *
     * Expects iterator on previous token
     * Leaves iterator to last used token - closing parenthesis
     */
    private function parseDirectives() : DirectiveSet
    {
        $directives = [];

        while ($this->tokenizer->peekNext()->getType() === TokenType::DIRECTIVE) {
            $this->tokenizer->getNext();

            $dirName = $this->tokenizer->getCurrent()->getValue();
            $dirArguments = null;

            if ($this->tokenizer->peekNext()->getType() === TokenType::PAR_O) {
                $this->tokenizer->getNext();
                $dirArguments = $this->parseArguments();
            }

            $directives[] = new Directive($dirName, $dirArguments);
        }

        return new DirectiveSet($directives);
    }

    /**
     * Parses argument list.
     *
     * Expects iterator on previous token - opening parenthesis
     * Leaves iterator to last used token - closing parenthesis
     */
    private function parseArguments() : ArgumentValueSet
    {
        $arguments = [];

        while ($this->tokenizer->peekNext()->getType() !== TokenType::PAR_C) {
            if ($this->tokenizer->getNext()->getType() !== TokenType::NAME) {
                throw new ExpectedArgumentName(
                    $this->tokenizer->getCurrent()->getLocation(),
                    $this->tokenizer->getCurrent()->getType(),
                );
            }

            $name = $this->tokenizer->getCurrent()->getValue();

            if (\array_key_exists($name, $arguments)) {
                throw new DuplicateArgument($name, $this->tokenizer->getCurrent()->getLocation());
            }

            $this->tokenizer->assertNext(TokenType::COLON, ExpectedColon::class);
            $value = $this->parseValue(false);

            $arguments[$name] = new ArgumentValue($value, $name);
        }

        $this->tokenizer->getNext();

        return new ArgumentValueSet($arguments);
    }

    /**
     * Parses value - either literal value or variable.
     *
     * Expects iterator on previous token
     * Leaves iterator to last used token - last token in value definition
     *
     * @param bool $literalOnly
     */
    private function parseValue(bool $literalOnly) : Value
    {
        switch ($this->tokenizer->getNext()->getType()) {
            case TokenType::VARIABLE:
                if ($literalOnly) {
                    throw new ExpectedLiteralValue(
                        $this->tokenizer->getCurrent()->getLocation(),
                        $this->tokenizer->getCurrent()->getType(),
                    );
                }

                return new VariableRef($this->tokenizer->getCurrent()->getValue());
            case TokenType::NAME:
                return match ($this->tokenizer->getCurrent()->getValue()) {
                    TokenType::TRUE->value => new Literal(true),
                    TokenType::FALSE->value => new Literal(false),
                    TokenType::NULL->value => new Literal(null),
                    default => new EnumLiteral($this->tokenizer->getCurrent()->getValue()),
                };
            case TokenType::STRING:
                return new Literal($this->tokenizer->getCurrent()->getValue());
            case TokenType::INT:
                return new Literal((int) $this->tokenizer->getCurrent()->getValue());
            case TokenType::FLOAT:
                return new Literal((float) $this->tokenizer->getCurrent()->getValue());
            case TokenType::SQU_O:
                $values = [];

                while ($this->tokenizer->peekNext()->getType() !== TokenType::SQU_C) {
                    $values[] = $this->parseValue($literalOnly);
                }

                $this->tokenizer->getNext();

                return new ListVal($values);
            case TokenType::CUR_O:
                $values = new \stdClass();

                while ($this->tokenizer->peekNext()->getType() !== TokenType::CUR_C) {
                    $name = $this->tokenizer->assertNext(TokenType::NAME, ExpectedFieldName::class)->getValue();
                    $this->tokenizer->assertNext(TokenType::COLON, ExpectedColon::class);
                    $values->{$name} = $this->parseValue($literalOnly);
                }

                $this->tokenizer->getNext();

                return new ObjectVal($values);
            default:
                throw new ExpectedValue(
                    $this->tokenizer->getNext()->getLocation(),
                    $this->tokenizer->getCurrent()->getType(),
                );
        }
    }

    /**
     * Parses type reference with modifiers.
     *
     * Expects iterator on previous token
     * Leaves iterator to last used token - last token in type definition
     *
     */
    private function parseType() : TypeRef
    {
        switch ($this->tokenizer->getNext()->getType()) {
            case TokenType::NAME:
                $type = new NamedTypeRef($this->tokenizer->getCurrent()->getValue());

                break;
            case TokenType::SQU_O:
                $type = new ListTypeRef($this->parseType());
                $this->tokenizer->assertNext(TokenType::SQU_C, ExpectedClosingBracket::class);

                break;
            default:
                throw new ExpectedType(
                    $this->tokenizer->getCurrent()->getLocation(),
                    $this->tokenizer->getCurrent()->getType(),
                );
        }

        if ($this->tokenizer->peekNext()->getType() === TokenType::EXCL) {
            $this->tokenizer->getNext();

            $type = new NotNullRef($type);
        }

        return $type;
    }

    /**
     * Parses type reference without modifiers
     *
     * Expects iterator on previous token
     * Leaves iterator to last used token - last token in type definition
     *
     */
    private function parseNamedType() : NamedTypeRef
    {
        if ($this->tokenizer->getNext()->getType() !== TokenType::NAME) {
            throw new ExpectedNamedType(
                $this->tokenizer->getCurrent()->getLocation(),
                $this->tokenizer->getCurrent()->getType(),
            );
        }

        return new NamedTypeRef($this->tokenizer->getCurrent()->getValue());
    }
}
