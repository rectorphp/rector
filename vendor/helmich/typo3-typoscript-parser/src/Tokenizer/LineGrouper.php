<?php

declare (strict_types=1);
namespace RectorPrefix20220418\Helmich\TypoScriptParser\Tokenizer;

/**
 * Class LineGrouper
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Tokenizer
 */
class LineGrouper
{
    /** @var TokenInterface[][] */
    private $tokensByLine = [];
    /**
     * @param TokenInterface[] $tokens
     */
    public function __construct(array $tokens)
    {
        foreach ($tokens as $token) {
            if (!\array_key_exists($token->getLine(), $this->tokensByLine)) {
                $this->tokensByLine[$token->getLine()] = [];
            }
            $this->tokensByLine[$token->getLine()][] = $token;
        }
    }
    /**
     * @return TokenInterface[][]
     */
    public function getLines() : array
    {
        return $this->tokensByLine;
    }
}
