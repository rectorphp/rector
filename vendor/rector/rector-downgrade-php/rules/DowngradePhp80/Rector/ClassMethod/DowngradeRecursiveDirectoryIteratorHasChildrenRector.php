<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeRecursiveDirectoryIteratorHasChildrenRector\DowngradeRecursiveDirectoryIteratorHasChildrenRectorTest
 */
final class DowngradeRecursiveDirectoryIteratorHasChildrenRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;
    public function __construct(FamilyRelationsAnalyzer $familyRelationsAnalyzer)
    {
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove bool type hint on child of RecursiveDirectoryIterator hasChildren allowLinks parameter', [new CodeSample(<<<'CODE_SAMPLE'
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
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        foreach ($node->getMethods() as $classMethod) {
            if (!$this->nodeNameResolver->isName($classMethod, 'hasChildren')) {
                continue;
            }
            if (!isset($classMethod->params[0])) {
                continue;
            }
            $ancestorClassNames = $this->familyRelationsAnalyzer->getClassLikeAncestorNames($node);
            if (!\in_array('RecursiveDirectoryIterator', $ancestorClassNames, \true)) {
                continue;
            }
            if ($classMethod->params[0]->type === null) {
                continue;
            }
            $classMethod->params[0]->type = null;
            return $node;
        }
        return null;
    }
}
