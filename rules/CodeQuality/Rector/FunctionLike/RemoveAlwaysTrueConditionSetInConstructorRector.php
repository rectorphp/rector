<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FunctionLike;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\PHPStan\Type\StaticTypeAnalyzer;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://phpstan.org/r/e909844a-084e-427e-92ac-fed3c2aeabab
 *
 * @see \Rector\Tests\CodeQuality\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\RemoveAlwaysTrueConditionSetInConstructorRectorTest
 */
final class RemoveAlwaysTrueConditionSetInConstructorRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\StaticTypeAnalyzer
     */
    private $staticTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(StaticTypeAnalyzer $staticTypeAnalyzer, TypeFactory $typeFactory)
    {
        $this->staticTypeAnalyzer = $staticTypeAnalyzer;
        $this->typeFactory = $typeFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('If conditions is always true, perform the content right away', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [If_::class];
    }
    /**
     * @param If_ $node
     * @return null|If_|Stmt[]
     */
    public function refactor(Node $node)
    {
        $ifStmt = $this->matchTruableIf($node);
        if (!$ifStmt instanceof If_) {
            return null;
        }
        if ($ifStmt->stmts === []) {
            $this->removeNode($ifStmt);
            return $ifStmt;
        }
        return $ifStmt->stmts;
    }
    /**
     * @return \PhpParser\Node\Stmt\If_|null
     */
    private function matchTruableIf(If_ $if)
    {
        // just one if
        if ($if->elseifs !== []) {
            return null;
        }
        // there is some else
        if ($if->else !== null) {
            return null;
        }
        // only property fetch, because of constructor set
        if (!$if->cond instanceof PropertyFetch) {
            return null;
        }
        $propertyFetchType = $this->resolvePropertyFetchType($if->cond);
        if (!$this->staticTypeAnalyzer->isAlwaysTruableType($propertyFetchType)) {
            return null;
        }
        return $if;
    }
    private function resolvePropertyFetchType(PropertyFetch $propertyFetch) : Type
    {
        $classLike = $this->betterNodeFinder->findParentType($propertyFetch, Class_::class);
        if (!$classLike instanceof Class_) {
            return new MixedType();
        }
        $propertyName = $this->getName($propertyFetch);
        if ($propertyName === null) {
            return new MixedType();
        }
        $property = $classLike->getProperty($propertyName);
        if (!$property instanceof Property) {
            return new MixedType();
        }
        // anything but private can be changed from outer scope
        if (!$property->isPrivate()) {
            return new MixedType();
        }
        // set in constructor + changed in class
        $propertyType = $this->resolvePropertyTypeAfterConstructor($classLike, $propertyName);
        $resolvedTypes = [$propertyType];
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
    private function resolvePropertyTypeAfterConstructor(Class_ $class, string $propertyName) : Type
    {
        $propertyTypeFromConstructor = null;
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if ($constructClassMethod !== null) {
            $propertyTypeFromConstructor = $this->resolveAssignedTypeInStmtsByPropertyName((array) $constructClassMethod->stmts, $propertyName);
        }
        if ($propertyTypeFromConstructor !== null) {
            return $propertyTypeFromConstructor;
        }
        // undefined property is null by default
        return new NullType();
    }
    /**
     * @param Stmt[] $stmts
     */
    private function resolveAssignedTypeInStmtsByPropertyName(array $stmts, string $propertyName) : ?Type
    {
        $resolvedTypes = [];
        $this->traverseNodesWithCallable($stmts, function (Node $node) use($propertyName, &$resolvedTypes) : ?int {
            if ($node instanceof ClassMethod && $this->isName($node, MethodName::CONSTRUCT)) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }
            if (!$this->isPropertyFetchAssignOfPropertyName($node, $propertyName)) {
                return null;
            }
            if (!$node instanceof Assign) {
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
    private function isPropertyFetchAssignOfPropertyName(Node $node, string $propertyName) : bool
    {
        if (!$node instanceof Assign) {
            return \false;
        }
        if (!$node->var instanceof PropertyFetch) {
            return \false;
        }
        return $this->isName($node->var, $propertyName);
    }
}
