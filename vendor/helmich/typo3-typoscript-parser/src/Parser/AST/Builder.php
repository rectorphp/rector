<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST;

use PhpParser\Node\Stmt\Nop;
/**
 * Helper class for quickly building AST nodes
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\AST
 */
class Builder
{
    /** @var Operator\Builder */
    private $operatorBuilder;
    /**
     * Builder constructor.
     */
    public function __construct()
    {
        $this->operatorBuilder = new \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\Operator\Builder();
    }
    /**
     * @param string      $condition
     * @param Statement[] $if
     * @param Statement[] $else
     * @param int         $line
     * @return ConditionalStatement
     */
    public function condition($condition, $if, $else, $line) : \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement
    {
        return new \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement($condition, $if, $else, $line);
    }
    /**
     * @param string $comment
     * @param int $line
     */
    public function comment($comment, $line) : \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\Comment
    {
        return new \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\Comment($comment, $line);
    }
    /**
     * @param string $comment
     * @param int $line
     */
    public function multilineComment($comment, $line) : \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\MultilineComment
    {
        return new \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\MultilineComment($comment, $line);
    }
    /**
     * @param int $line
     */
    public function nop($line) : \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\NopStatement
    {
        return new \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\NopStatement($line);
    }
    /**
     * @param string      $directory
     * @param string|null $extensions
     * @param string|null $condition
     * @param int         $line
     * @return DirectoryIncludeStatement
     */
    public function includeDirectory($directory, $extensions, $condition, $line) : \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\DirectoryIncludeStatement
    {
        return new \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\DirectoryIncludeStatement($directory, $extensions, $condition, $line);
    }
    /**
     * @param string      $file
     * @param boolean     $newSyntax
     * @param string|null $condition
     * @param int         $line
     * @return FileIncludeStatement
     */
    public function includeFile($file, $newSyntax, $condition, $line) : \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\FileIncludeStatement
    {
        return new \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\FileIncludeStatement($file, $newSyntax, $condition, $line);
    }
    /**
     * @param ObjectPath  $path
     * @param Statement[] $statements
     * @param int         $line
     * @return NestedAssignment
     */
    public function nested($path, $statements, $line) : \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\NestedAssignment
    {
        return new \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\NestedAssignment($path, $statements, $line);
    }
    /**
     * @param string $value
     * @return Scalar
     */
    public function scalar($value) : \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\Scalar
    {
        return new \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\Scalar($value);
    }
    /**
     * @param string $absolute
     * @param string $relative
     * @return ObjectPath
     */
    public function path($absolute, $relative) : \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\ObjectPath
    {
        return new \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\ObjectPath($absolute, $relative);
    }
    /**
     * @return Operator\Builder
     */
    public function op() : \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\Operator\Builder
    {
        return $this->operatorBuilder;
    }
}
