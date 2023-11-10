<?php

declare(strict_types = 1);

namespace Graphpinator\Parser;

use \Graphpinator\Tokenizer\Token;

final class TokenContainer implements \IteratorAggregate
{
    private array $tokens = [];
    private int $currentIndex = 0;

    public function __construct(\Graphpinator\Source\Source $source)
    {
        $tokenizer = new \Graphpinator\Tokenizer\Tokenizer($source, true, false);

        foreach ($tokenizer as $token) {
            $this->tokens[] = $token;
        }
    }

    public function hasNext() : bool
    {
        return \array_key_exists($this->currentIndex + 1, $this->tokens);
    }

    public function isEmpty() : bool
    {
        return \count($this->tokens) === 0;
    }

    public function getCurrent() : Token
    {
        return $this->tokens[$this->currentIndex];
    }

    public function getPrev() : Token
    {
        \assert(\array_key_exists($this->currentIndex - 1, $this->tokens));

        --$this->currentIndex;

        return $this->tokens[$this->currentIndex];
    }

    public function getNext() : Token
    {
        if (!$this->hasNext()) {
            throw new \Graphpinator\Parser\Exception\UnexpectedEnd($this->getCurrent()->getLocation());
        }

        ++$this->currentIndex;

        return $this->tokens[$this->currentIndex];
    }

    public function peekNext() : Token
    {
        if (!$this->hasNext()) {
            throw new \Graphpinator\Parser\Exception\UnexpectedEnd($this->getCurrent()->getLocation());
        }

        return $this->tokens[$this->currentIndex + 1];
    }

    public function assertNext(\Graphpinator\Tokenizer\TokenType $tokenType, string $exceptionClass) : Token
    {
        $token = $this->getNext();

        if ($token->getType() === $tokenType) {
            return $token;
        }

        throw new $exceptionClass($token->getLocation(), $token->getType());
    }

    public function assertNextValue(\Graphpinator\Tokenizer\TokenType $tokenType, string $value, string $exceptionClass) : Token
    {
        $token = $this->getNext();

        if ($token->getType() === $tokenType && $token->getValue() === $value) {
            return $token;
        }

        throw new $exceptionClass($token->getLocation(), $token->getType());
    }

    public function getIterator() : \ArrayIterator
    {
        return new \ArrayIterator($this->tokens);
    }
}
