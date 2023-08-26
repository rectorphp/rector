<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/Wgj19
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\ClassMethod\RemoveReturnTypeDeclarationFromCloneRector\RemoveReturnTypeDeclarationFromCloneRectorTest
 */
final class RemoveReturnTypeDeclarationFromCloneRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove return type from __clone() method', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function __clone(): void
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function __clone()
    {
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->returnType instanceof Node) {
            return null;
        }
        if (!$this->isName($node, '__clone')) {
            return null;
        }
        $node->returnType = null;
        return $node;
    }
}
