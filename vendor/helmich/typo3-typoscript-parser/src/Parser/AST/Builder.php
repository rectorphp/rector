<?php

declare (strict_types=1);
namespace RectorPrefix20220527\Helmich\TypoScriptParser\Parser\AST;

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
        $this->operatorBuilder = new Operator\Builder();
    }
    /**
     * @param string      $condition
     * @param Statement[] $if
     * @param Statement[] $else
     * @param int         $line
     * @return ConditionalStatement
     */
    public function condition(string $condition, array $if, array $else, int $line) : ConditionalStatement
    {
        return new ConditionalStatement($condition, $if, $else, $line);
    }
    public function comment(string $comment, int $line) : Comment
    {
        return new Comment($comment, $line);
    }
    public function multilineComment(string $comment, int $line) : MultilineComment
    {
        return new MultilineComment($comment, $line);
    }
    public function nop(int $line) : NopStatement
    {
        return new NopStatement($line);
    }
    /**
     * @param string      $directory
     * @param string|null $extensions
     * @param string|null $condition
     * @param int         $line
     * @return DirectoryIncludeStatement
     */
    public function includeDirectory(string $directory, ?string $extensions, ?string $condition, int $line) : DirectoryIncludeStatement
    {
        return new DirectoryIncludeStatement($directory, $extensions, $condition, $line);
    }
    /**
     * @param string      $file
     * @param boolean     $newSyntax
     * @param string|null $condition
     * @param int         $line
     * @return FileIncludeStatement
     */
    public function includeFile(string $file, bool $newSyntax, ?string $condition, int $line) : FileIncludeStatement
    {
        return new FileIncludeStatement($file, $newSyntax, $condition, $line);
    }
    /**
     * @param ObjectPath  $path
     * @param Statement[] $statements
     * @param int         $line
     * @return NestedAssignment
     */
    public function nested(ObjectPath $path, array $statements, int $line) : NestedAssignment
    {
        return new NestedAssignment($path, $statements, $line);
    }
    /**
     * @param string $value
     * @return Scalar
     */
    public function scalar(string $value) : Scalar
    {
        return new Scalar($value);
    }
    /**
     * @param string $absolute
     * @param string $relative
     * @return ObjectPath
     */
    public function path(string $absolute, string $relative) : ObjectPath
    {
        return new ObjectPath($absolute, $relative);
    }
    /**
     * @return Operator\Builder
     */
    public function op() : Operator\Builder
    {
        return $this->operatorBuilder;
    }
}
