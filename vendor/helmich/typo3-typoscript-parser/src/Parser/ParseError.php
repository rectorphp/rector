<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Helmich\TypoScriptParser\Parser;

use Exception;
class ParseError extends \Exception
{
    /** @var int|null */
    private $sourceLine;
    public function __construct(string $message = "", int $code = 0, ?int $line = null, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->sourceLine = $line;
    }
    public function getSourceLine() : ?int
    {
        return $this->sourceLine;
    }
}
