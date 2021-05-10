<?php

declare (strict_types=1);
namespace PHPStan\PhpDocParser\Parser;

use PHPStan\PhpDocParser\Lexer\Lexer;
class ParserException extends \Exception
{
    /** @var string */
    private $currentTokenValue;
    /** @var int */
    private $currentTokenType;
    /** @var int */
    private $currentOffset;
    /** @var int */
    private $expectedTokenType;
    public function __construct(string $currentTokenValue, int $currentTokenType, int $currentOffset, int $expectedTokenType)
    {
        $this->currentTokenValue = $currentTokenValue;
        $this->currentTokenType = $currentTokenType;
        $this->currentOffset = $currentOffset;
        $this->expectedTokenType = $expectedTokenType;
        $json = \json_encode($currentTokenValue, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES);
        \assert($json !== \false);
        parent::__construct(\sprintf('Unexpected token %s, expected %s at offset %d', $json, \PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_LABELS[$expectedTokenType], $currentOffset));
    }
    public function getCurrentTokenValue() : string
    {
        return $this->currentTokenValue;
    }
    public function getCurrentTokenType() : int
    {
        return $this->currentTokenType;
    }
    public function getCurrentOffset() : int
    {
        return $this->currentOffset;
    }
    public function getExpectedTokenType() : int
    {
        return $this->expectedTokenType;
    }
}
