<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Helmich\TypoScriptParser\Parser;

use ArrayObject;
use RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\ObjectPath;
use RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\RootObjectPath;
use RectorPrefix20211020\Helmich\TypoScriptParser\Tokenizer\TokenInterface;
class ParserState
{
    /** @var ObjectPath */
    private $context;
    /** @var ArrayObject */
    private $statements;
    /** @var TokenStream */
    private $tokens;
    public function __construct(\RectorPrefix20211020\Helmich\TypoScriptParser\Parser\TokenStream $tokens, \ArrayObject $statements = null)
    {
        if ($statements === null) {
            $statements = new \ArrayObject();
        }
        $this->statements = $statements;
        $this->tokens = $tokens;
        $this->context = new \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\RootObjectPath();
    }
    /**
     * @param \Helmich\TypoScriptParser\Parser\AST\ObjectPath $context
     */
    public function withContext($context) : self
    {
        $clone = clone $this;
        $clone->context = $context;
        return $clone;
    }
    /**
     * @param \ArrayObject $statements
     */
    public function withStatements($statements) : self
    {
        $clone = clone $this;
        $clone->statements = $statements;
        return $clone;
    }
    /**
     * @param int $lookAhead
     * @return TokenInterface
     */
    public function token($lookAhead = 0) : \RectorPrefix20211020\Helmich\TypoScriptParser\Tokenizer\TokenInterface
    {
        return $this->tokens->current($lookAhead);
    }
    /**
     * @param int $increment
     * @return void
     */
    public function next($increment = 1) : void
    {
        $this->tokens->next($increment);
    }
    /**
     * @return bool
     */
    public function hasNext() : bool
    {
        return $this->tokens->valid();
    }
    /**
     * @return ObjectPath
     */
    public function context() : \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\ObjectPath
    {
        return $this->context;
    }
    /**
     * @return ArrayObject
     */
    public function statements() : \ArrayObject
    {
        return $this->statements;
    }
}
