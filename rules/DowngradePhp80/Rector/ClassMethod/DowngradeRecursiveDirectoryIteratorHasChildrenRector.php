<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeRecursiveDirectoryIteratorHasChildrenRector\DowngradeRecursiveDirectoryIteratorHasChildrenRectorTest
 */
final class DowngradeRecursiveDirectoryIteratorHasChildrenRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    public function __construct(\Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer $familyRelationsAnalyzer)
    {
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove bool type hint on child of RecursiveDirectoryIterator hasChildren allowLinks parameter', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class RecursiveDirectoryIteratorChild extends \RecursiveDirectoryIterator
{
    public function hasChildren(bool $allowLinks = false): bool
    {
        return true;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class RecursiveDirectoryIteratorChild extends \RecursiveDirectoryIterator
{
    public function hasChildren($allowLinks = false): bool
    {
        return true;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodeNameResolver->isName($node, 'hasChildren')) {
            return null;
        }
        if (!isset($node->params[0])) {
            return null;
        }
        $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        $ancestorClassNames = $this->familyRelationsAnalyzer->getClassLikeAncestorNames($classLike);
        if (!\in_array('RecursiveDirectoryIterator', $ancestorClassNames, \true)) {
            return null;
        }
        if ($node->params[0]->type === null) {
            return null;
        }
        $node->params[0]->type = null;
        return $node;
    }
}
