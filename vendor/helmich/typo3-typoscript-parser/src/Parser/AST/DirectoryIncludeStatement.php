<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST;

/**
 * Include statements that includes many TypoScript files from a directory.
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\AST
 */
class DirectoryIncludeStatement extends \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\IncludeStatement
{
    /**
     * The directory to include from.
     *
     * @var string
     */
    public $directory;
    /**
     * Conditional statement that is attached to this include
     *
     * @var string|null
     */
    public $condition;
    /**
     * Same as extensions
     *
     * @var string|null
     * @deprecated Use `extensions` instead
     */
    public $extension = null;
    /**
     * An optional file extension filter. May be NULL.
     *
     * @var string|null
     */
    public $extensions = null;
    /**
     * Constructs a new directory include statement.
     *
     * @param string      $directory  The directory to include from.
     * @param string|null $extensions The file extension filter. MAY be NULL.
     * @param string|null $condition  Conditional statement that is attached to this include
     * @param int         $sourceLine The original source line.
     *
     * @psalm-suppress DeprecatedProperty
     */
    public function __construct(string $directory, ?string $extensions, ?string $condition, int $sourceLine)
    {
        parent::__construct($sourceLine);
        $this->directory = $directory;
        $this->extension = $extensions;
        $this->extensions = $extensions;
        $this->condition = $condition;
    }
}
