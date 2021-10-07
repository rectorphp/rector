<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\StaticTypeAnalyzer;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://phpstan.org/r/e909844a-084e-427e-92ac-fed3c2aeabab
 *
 * @see \Rector\Tests\CodeQuality\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\RemoveAlwaysTrueConditionSetInConstructorRectorTest
 */
final class RemoveAlwaysTrueConditionSetInConstructorRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Type\StaticTypeAnalyzer
     */
    private $staticTypeAnalyzer;
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(\Rector\NodeTypeResolver\PHPStan\Type\StaticTypeAnalyzer $staticTypeAnalyzer, \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory)
    {
        $this->staticTypeAnalyzer = $staticTypeAnalyzer;
        $this->typeFactory = $typeFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('If conditions is always true, perform the content right away', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private $value;

    public function __construct(stdClass $value)
    {
        $this->value = $value;
    }

    public function go()
    {
        if ($this->value) {
            return 'yes';
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private $value;

    public function __construct(stdClass $value)
    {
        $this->value = $value;
    }

    public function go()
    {
        return 'yes';
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
        return [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Expr\Closure::class];
    }
    /**
     * @param ClassMethod|Closure $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->stmts === null) {
            return null;
        }
        if ($node->stmts === []) {
            return null;
        }
        $haveNodeChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if ($stmt instanceof \PhpParser\Node\Stmt\Expression) {
                $stmt = $stmt->expr;
            }
            if (!$this->isAlwaysTruableNode($stmt)) {
                continue;
            }
            /** @var If_ $stmt */
            if (\count($stmt->stmts) === 1) {
                $node->stmts[$key] = $stmt->stmts[0];
                continue;
            }
            $haveNodeChanged = \true;
            // move all nodes one level up
            \array_splice($node->stmts, $key, \count($stmt->stmts) - 1, $stmt->stmts);
        }
        if ($haveNodeChanged) {
            return $node;
        }
        return null;
    }
    private function isAlwaysTruableNode(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Stmt\If_) {
            return \false;
        }
        // just one if
        if ($node->elseifs !== []) {
            return \false;
        }
        // there is some else
        if ($node->else !== null) {
            return \false;
        }
        // only property fetch, because of constructor set
        if (!$node->cond instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \false;
        }
        $propertyFetchType = $this->resolvePropertyFetchType($node->cond);
        return $this->staticTypeAnalyzer->isAlwaysTruableType($propertyFetchType);
    }
    private function resolvePropertyFetchType(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : \PHPStan\Type\Type
    {
        $classLike = $propertyFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return new \PHPStan\Type\MixedType();
        }
        $propertyName = $this->getName($propertyFetch);
        if ($propertyName === null) {
            return new \PHPStan\Type\MixedType();
        }
        $property = $classLike->getProperty($propertyName);
        if (!$property instanceof \PhpParser\Node\Stmt\Property) {
            return new \PHPStan\Type\MixedType();
        }
        // anything but private can be changed from outer scope
        if (!$property->isPrivate()) {
            return new \PHPStan\Type\MixedType();
        }
        // set in constructor + changed in class
        $propertyTypeFromConstructor = $this->resolvePropertyTypeAfterConstructor($classLike, $propertyName);
        $resolvedTypes = [];
        $resolvedTypes[] = $propertyTypeFromConstructor;
        $defaultValue = $property->props[0]->default;
        if ($defaultValue !== null) {
            $resolvedTypes[] = $this->getType($defaultValue);
        }
        $resolveAssignedType = $this->resolveAssignedTypeInStmtsByPropertyName($classLike->stmts, $propertyName);
        if ($resolveAssignedType !== null) {
            $resolvedTypes[] = $resolveAssignedType;
        }
        return $this->typeFactory->createMixedPassedOrUnionTypeAndKeepConstant($resolvedTypes);
    }
    private function resolvePropertyTypeAfterConstructor(\PhpParser\Node\Stmt\Class_ $class, string $propertyName) : \PHPStan\Type\Type
    {
        $propertyTypeFromConstructor = null;
        $constructClassMethod = $class->getMethod(\Rector\Core\ValueObject\MethodName::CONSTRUCT);
        if ($constructClassMethod !== null) {
            $propertyTypeFromConstructor = $this->resolveAssignedTypeInStmtsByPropertyName((array) $constructClassMethod->stmts, $propertyName);
        }
        if ($propertyTypeFromConstructor !== null) {
            return $propertyTypeFromConstructor;
        }
        // undefined property is null by default
        return new \PHPStan\Type\NullType();
    }
    /**
     * @param Stmt[] $stmts
     */
    private function resolveAssignedTypeInStmtsByPropertyName(array $stmts, string $propertyName) : ?\PHPStan\Type\Type
    {
        $resolvedTypes = [];
        $this->traverseNodesWithCallable($stmts, function (\PhpParser\Node $node) use($propertyName, &$resolvedTypes) : ?int {
            if ($node instanceof \PhpParser\Node\Stmt\ClassMethod && $this->isName($node, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
                return \PhpParser\NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            if (!$this->isPropertyFetchAssignOfPropertyName($node, $propertyName)) {
                return null;
            }
            if (!$node instanceof \PhpParser\Node\Expr\Assign) {
                return null;
            }
            $resolvedTypes[] = $this->getType($node->expr);
            return null;
        });
        if ($resolvedTypes === []) {
            return null;
        }
        return $this->typeFactory->createMixedPassedOrUnionTypeAndKeepConstant($resolvedTypes);
    }
    /**
     * E.g. $this->{value} = x
     */
    private function isPropertyFetchAssignOfPropertyName(\PhpParser\Node $node, string $propertyName) : bool
    {
        if (!$node instanceof \PhpParser\Node\Expr\Assign) {
            return \false;
        }
        if (!$node->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            return \false;
        }
        return $this->isName($node->var, $propertyName);
    }
}
