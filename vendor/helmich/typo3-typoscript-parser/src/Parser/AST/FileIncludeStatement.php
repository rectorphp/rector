<?php

declare (strict_types=1);
namespace RectorPrefix20220209\Helmich\TypoScriptParser\Parser\AST;

/**
 * Include statements that includes a single TypoScript file.
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\AST
 */
class FileIncludeStatement extends \RectorPrefix20220209\Helmich\TypoScriptParser\Parser\AST\IncludeStatement
{
    /**
     * The name of the file to include.
     *
     * @var string
     */
    public $filename;
    /**
     * Conditional statement that is attached to this include
     *
     * @var string|null
     */
    public $condition;
    /**
     * Determines if this statement uses the new @import syntax
     *
     * @var boolean
     */
    public $newSyntax;
    /**
     * Constructs a new include statement.
     *
     * @param string      $filename   The name of the file to include.
     * @param boolean     $newSyntax  Determines if this statement uses the new import syntax
     * @param string|null $condition  Conditional statement that is attached to this include
     * @param int         $sourceLine The original source line.
     */
    public function __construct(string $filename, bool $newSyntax, ?string $condition, int $sourceLine)
    {
        parent::__construct($sourceLine);
        $this->filename = $filename;
        $this->newSyntax = $newSyntax;
        $this->condition = $condition;
    }
}
