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
final class RemoveUnusedPromotedPropertyRector extends AbstractRector implements MinPhpVersionInterface
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
    public function __construct(PropertyFetchFinder $propertyFetchFinder, PropertyManipulator $propertyManipulator, VisibilityManipulator $visibilityManipulator)
    {
        $this->propertyFetchFinder = $propertyFetchFinder;
        $this->propertyManipulator = $propertyManipulator;
        $this->visibilityManipulator = $visibilityManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused promoted property', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        // is attribute? skip it
        if ($node->attrGroups !== []) {
            return null;
        }
        $hasRemovedProperty = \false;
        foreach ($constructClassMethod->getParams() as $param) {
            // only private local scope; removing public property might be dangerous
            if (!$this->visibilityManipulator->hasVisibility($param, Visibility::PRIVATE)) {
                continue;
            }
            if ($this->propertyManipulator->isPropertyUsedInReadContext($node, $param)) {
                continue;
            }
            $paramName = $this->getName($param);
            $propertyFetches = $this->propertyFetchFinder->findLocalPropertyFetchesByName($node, $paramName);
            if ($propertyFetches !== []) {
                continue;
            }
            // is variable used? only remove property, keep param
            $variable = $this->betterNodeFinder->findVariableOfName((array) $constructClassMethod->stmts, $paramName);
            if ($variable instanceof Variable) {
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
        return PhpVersionFeature::PROPERTY_PROMOTION;
    }
}
