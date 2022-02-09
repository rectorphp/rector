<?php

declare (strict_types=1);
namespace RectorPrefix20220209\Helmich\TypoScriptParser\Parser\AST;

/**
 * Abstract TypoScript statement.
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\AST
 */
abstract class Statement
{
    /**
     * The original source line. Useful for tracing and debugging.
     *
     * @var int
     */
    public $sourceLine;
    /**
     * Base statement constructor.
     *
     * @param int $sourceLine The original source line.
     */
    public function __construct(int $sourceLine)
    {
        if ($sourceLine <= 0) {
            throw new \InvalidArgumentException(\sprintf('Source line must be greater than 0 for %s statement (is: %d)!', \get_class($this), $sourceLine));
        }
        $this->sourceLine = $sourceLine;
    }
}
/**
 * Abstract TypoScript statement.
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\AST
 */
\class_alias('RectorPrefix20220209\\Helmich\\TypoScriptParser\\Parser\\AST\\Statement', 'Helmich\\TypoScriptParser\\Parser\\AST\\Statement', \false);
