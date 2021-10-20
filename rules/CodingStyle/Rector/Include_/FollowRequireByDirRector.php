<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Include_;

use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Include_\FollowRequireByDirRector\FollowRequireByDirRectorTest
 */
final class FollowRequireByDirRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('include/require should be followed by absolute path', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        require 'autoload.php';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        require __DIR__ . '/autoload.php';
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Include_::class];
    }
    /**
     * @param Include_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->expr instanceof \PhpParser\Node\Expr\BinaryOp\Concat && $node->expr->left instanceof \PhpParser\Node\Scalar\String_ && $this->isRefactorableStringPath($node->expr->left)) {
            $node->expr->left = $this->prefixWithDir($node->expr->left);
            return $node;
        }
        if ($node->expr instanceof \PhpParser\Node\Scalar\String_ && $this->isRefactorableStringPath($node->expr)) {
            $node->expr = $this->prefixWithDir($node->expr);
            return $node;
        }
        // nothing we can do
        return null;
    }
    private function isRefactorableStringPath(\PhpParser\Node\Scalar\String_ $string) : bool
    {
        return \strncmp($string->value, 'phar://', \strlen('phar://')) !== 0;
    }
    private function prefixWithDir(\PhpParser\Node\Scalar\String_ $string) : \PhpParser\Node\Expr\BinaryOp\Concat
    {
        $this->removeExtraDotSlash($string);
        $this->prependSlashIfMissing($string);
        return new \PhpParser\Node\Expr\BinaryOp\Concat(new \PhpParser\Node\Scalar\MagicConst\Dir(), $string);
    }
    /**
     * Remove "./" which would break the path
     */
    private function removeExtraDotSlash(\PhpParser\Node\Scalar\String_ $string) : void
    {
        if (\strncmp($string->value, './', \strlen('./')) !== 0) {
            return;
        }
        $string->value = \RectorPrefix20211020\Nette\Utils\Strings::replace($string->value, '#^\\.\\/#', '/');
    }
    private function prependSlashIfMissing(\PhpParser\Node\Scalar\String_ $string) : void
    {
        if (\strncmp($string->value, '/', \strlen('/')) === 0) {
            return;
        }
        $string->value = '/' . $string->value;
    }
}
