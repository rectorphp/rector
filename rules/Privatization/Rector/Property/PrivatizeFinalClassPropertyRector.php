<?php

declare (strict_types=1);
namespace Rector\Privatization\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Property;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector\PrivatizeFinalClassPropertyRectorTest
 */
final class PrivatizeFinalClassPropertyRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    public function __construct(\Rector\Privatization\NodeManipulator\VisibilityManipulator $visibilityManipulator, \Rector\Core\PhpParser\AstResolver $astResolver, \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->visibilityManipulator = $visibilityManipulator;
        $this->astResolver = $astResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change property to private if possible', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    protected $value;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private $value;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $classLike = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Class_::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return null;
        }
        if (!$classLike->isFinal()) {
            return null;
        }
        if ($this->shouldSkipProperty($node)) {
            return null;
        }
        if ($classLike->extends === null) {
            $this->visibilityManipulator->makePrivate($node);
            return $node;
        }
        if ($this->isPropertyVisibilityGuardedByParent($node, $classLike)) {
            return null;
        }
        $this->visibilityManipulator->makePrivate($node);
        return $node;
    }
    private function shouldSkipProperty(\PhpParser\Node\Stmt\Property $property) : bool
    {
        if (\count($property->props) !== 1) {
            return \true;
        }
        return !$property->isProtected();
    }
    private function isPropertyVisibilityGuardedByParent(\PhpParser\Node\Stmt\Property $property, \PhpParser\Node\Stmt\Class_ $class) : bool
    {
        if ($class->extends === null) {
            return \false;
        }
        /** @var Scope $scope */
        $scope = $property->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::SCOPE);
        /** @var ClassReflection $classReflection */
        $classReflection = $scope->getClassReflection();
        $propertyName = $this->getName($property);
        $className = (string) $this->nodeNameResolver->getName($class);
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasProperty($propertyName)) {
                return \true;
            }
            if ($this->isFoundInParentClassMethods($parentClassReflection, $propertyName, $className)) {
                return \true;
            }
        }
        return \false;
    }
    private function isFoundInParentClassMethods(\PHPStan\Reflection\ClassReflection $parentClassReflection, string $propertyName, string $className) : bool
    {
        $classLike = $this->astResolver->resolveClassFromName($parentClassReflection->getName());
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \false;
        }
        $methods = $classLike->getMethods();
        foreach ($methods as $method) {
            $isFound = $this->isFoundInMethodStmts((array) $method->stmts, $propertyName, $className);
            if ($isFound) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Stmt[] $stmts
     */
    private function isFoundInMethodStmts(array $stmts, string $propertyName, string $className) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($stmts, function (\PhpParser\Node $subNode) use($propertyName, $className) : bool {
            if (!$this->propertyFetchAnalyzer->isPropertyFetch($subNode)) {
                return \false;
            }
            /** @var PropertyFetch|StaticPropertyFetch $subNode */
            if ($subNode instanceof \PhpParser\Node\Expr\PropertyFetch) {
                if (!$subNode->var instanceof \PhpParser\Node\Expr\Variable) {
                    return \false;
                }
                if (!$this->nodeNameResolver->isName($subNode->var, 'this')) {
                    return \false;
                }
                return $this->nodeNameResolver->isName($subNode, $propertyName);
            }
            if (!$this->nodeNameResolver->isNames($subNode->class, [\Rector\Core\Enum\ObjectReference::SELF()->getValue(), \Rector\Core\Enum\ObjectReference::STATIC()->getValue(), $className])) {
                return \false;
            }
            return $this->nodeNameResolver->isName($subNode->name, $propertyName);
        });
    }
}
