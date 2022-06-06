<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Core\ValueObject\Visibility;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector\RemoveUnusedPromotedPropertyRectorTest
 */
final class RemoveUnusedPromotedPropertyRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder
     */
    private $propertyFetchFinder;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\PropertyManipulator
     */
    private $propertyManipulator;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    public function __construct(\Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder $propertyFetchFinder, \Rector\Core\NodeManipulator\PropertyManipulator $propertyManipulator, \Rector\Privatization\NodeManipulator\VisibilityManipulator $visibilityManipulator)
    {
        $this->propertyFetchFinder = $propertyFetchFinder;
        $this->propertyManipulator = $propertyManipulator;
        $this->visibilityManipulator = $visibilityManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove unused promoted property', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private $someUnusedDependency,
        private $usedDependency
    ) {
    }

    public function getUsedDependency()
    {
        return $this->usedDependency;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct(
        private $usedDependency
    ) {
    }

    public function getUsedDependency()
    {
        return $this->usedDependency;
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $hasRemovedProperty = \false;
        if (!$this->isName($node, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return null;
        }
        $class = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        foreach ($node->getParams() as $param) {
            // only private local scope; removing public property might be dangerous
            if (!$this->visibilityManipulator->hasVisibility($param, \Rector\Core\ValueObject\Visibility::PRIVATE)) {
                continue;
            }
            if ($this->propertyManipulator->isPropertyUsedInReadContext($class, $param)) {
                continue;
            }
            $paramName = $this->getName($param);
            $propertyFetches = $this->propertyFetchFinder->findLocalPropertyFetchesByName($class, $paramName);
            if ($propertyFetches !== []) {
                continue;
            }
            // is variable used? only remove property, keep param
            $variable = $this->betterNodeFinder->findVariableOfName((array) $node->stmts, $paramName);
            if ($variable instanceof \PhpParser\Node\Expr\Variable) {
                $param->flags = 0;
                continue;
            }
            // remove param
            $this->removeNode($param);
            $hasRemovedProperty = \true;
        }
        if ($hasRemovedProperty) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::PROPERTY_PROMOTION;
    }
}
