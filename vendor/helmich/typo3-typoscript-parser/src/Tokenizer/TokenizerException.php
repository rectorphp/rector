<?php

declare (strict_types=1);
namespace RectorPrefix20220531\Helmich\TypoScriptParser\Tokenizer;

/**
 * An exception that represents an error during tokenization.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TypoScriptParser
 * @subpackage Tokenizer
 */
class TokenizerException extends \Exception
{
    /** @var int|null */
    private $sourceLine;
    /**
     * Constructs a new tokenizer exception.
     *
     * @param string          $message    The message text.
     * @param int             $code       The exception code.
     * @param \Exception|null $previous   A nested previous exception.
     * @param int|null        $sourceLine The original source line.
     */
    public function __construct(string $message = "", int $code = 0, \Exception $previous = null, int $sourceLine = null)
    {
        parent::__construct($message, $code, $previous);
        $this->sourceLine = $sourceLine;
    }
    /**
     * Gets the original source line.
     *
     * @return int|null The original source line.
     */
    public function getSourceLine() : ?int
    {
        return $this->sourceLine;
    }
}
