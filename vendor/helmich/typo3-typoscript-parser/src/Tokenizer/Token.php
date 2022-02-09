<?php

declare (strict_types=1);
namespace RectorPrefix20220209\Helmich\TypoScriptParser\Tokenizer;

class Token implements \RectorPrefix20220209\Helmich\TypoScriptParser\Tokenizer\TokenInterface
{
    /** @var string */
    private $type;
    /** @var string */
    private $value;
    /** @var int */
    private $line;
    /** @var int */
    private $column;
    /** @var array */
    private $patternMatches;
    /**
     * @param string $type
     * @param string $value
     * @param int    $line
     * @param int    $column
     * @param array  $patternMatches
     */
    public function __construct(string $type, string $value, int $line, int $column = 1, array $patternMatches = [])
    {
        $this->type = $type;
        $this->value = $value;
        $this->line = $line;
        $this->column = $column;
        $this->patternMatches = $patternMatches;
    }
    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }
    /**
     * @return string
     */
    public function getValue() : string
    {
        return $this->value;
    }
    /**
     * @param string $name
     * @return string|null
     */
    public function getSubMatch(string $name) : ?string
    {
        return isset($this->patternMatches[$name]) ? $this->patternMatches[$name] : null;
    }
    /**
     * @return int
     */
    public function getLine() : int
    {
        return $this->line;
    }
    /**
     * @return int
     */
    public function getColumn() : int
    {
        return $this->column;
    }
}
