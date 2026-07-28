<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Configuration\Parameter\FeatureFlags;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Property\RemoveDefaultValueFromAssignedPropertyRector\RemoveDefaultValueFromAssignedPropertyRectorTest
 */
final class RemoveDefaultValueFromAssignedPropertyRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ConstructorAssignDetector $constructorAssignDetector;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private PropertyFetchAnalyzer $propertyFetchAnalyzer;
    public function __construct(ConstructorAssignDetector $constructorAssignDetector, BetterNodeFinder $betterNodeFinder, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->constructorAssignDetector = $constructorAssignDetector;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove redundant default value from property that is always assigned in the constructor', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private ?SomeType $someType = null;

    public function __construct(SomeType $someType)
    {
        $this->someType = $someType;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    private ?SomeType $someType;

    public function __construct(SomeType $someType)
    {
        $this->someType = $someType;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $constructClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        // early return can skip the assign, so the default value is still needed
        if ($this->betterNodeFinder->hasInstancesOfInFunctionLikeScoped($constructClassMethod, Return_::class)) {
            return null;
        }
        $hasChanged = \false;
        $isFinal = $node->isFinal() || FeatureFlags::treatClassesAsFinal($node);
        foreach ($node->getProperties() as $property) {
            // untyped properties are handled by RemoveNullPropertyInitializationRector
            if (!$property->type instanceof Node) {
                continue;
            }
            if ($property->hooks !== []) {
                continue;
            }
            if (!$property->isPrivate() && !$isFinal) {
                continue;
            }
            foreach ($property->props as $propertyProperty) {
                if (!$propertyProperty->default instanceof Expr) {
                    continue;
                }
                $propertyName = $this->getName($propertyProperty);
                if (!$this->constructorAssignDetector->isPropertyAssigned($node, $propertyName)) {
                    continue;
                }
                // partial assign, e.g. $this->items['key'] = ...; keeps the default value required
                if ($this->isAssignedViaArrayDimFetch($node, $propertyName)) {
                    continue;
                }
                $propertyProperty->default = null;
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function isAssignedViaArrayDimFetch(Class_ $class, string $propertyName): bool
    {
        return $this->betterNodeFinder->findFirst($class, function (Node $subNode) use ($propertyName): bool {
            if (!$subNode instanceof Assign) {
                return \false;
            }
            $assignedExpr = $subNode->var;
            if (!$assignedExpr instanceof ArrayDimFetch) {
                return \false;
            }
            // unwrap nested dims, e.g. $this->items['first']['second'] = ...
            while ($assignedExpr instanceof ArrayDimFetch) {
                $assignedExpr = $assignedExpr->var;
            }
            return $this->propertyFetchAnalyzer->isLocalPropertyFetchName($assignedExpr, $propertyName);
        }) instanceof Assign;
    }
}
