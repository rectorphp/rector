<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeTraverser;
use PHPStan\Type\MixedType;
use PHPStan\Type\NullType;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\StaticTypeAnalyzer;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;

/**
 * @see https://phpstan.org/r/e909844a-084e-427e-92ac-fed3c2aeabab
 *
 * @see \Rector\CodeQuality\Tests\Rector\If_\RemoveAlwaysTrueConditionSetInConstructorRector\RemoveAlwaysTrueConditionSetInConstructorRectorTest
 */
final class RemoveAlwaysTrueConditionSetInConstructorRector extends AbstractRector
{
    /**
     * @var StaticTypeAnalyzer
     */
    private $staticTypeAnalyzer;

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    public function __construct(StaticTypeAnalyzer $staticTypeAnalyzer, TypeFactory $typeFactory)
    {
        $this->staticTypeAnalyzer = $staticTypeAnalyzer;
        $this->typeFactory = $typeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('If conditions is always true, perform the content right away', [
            new CodeSample(
                <<<'PHP'
final class SomeClass
{
    private $value;

    public function __construct($value)
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
PHP
                ,
                <<<'PHP'
final class SomeClass
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function go()
    {
        return 'yes';
    }
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Closure::class];
    }

    /**
     * @param ClassMethod|Closure $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null || $node->stmts === []) {
            return null;
        }

        $haveNodeChanged = false;
        foreach ((array) $node->stmts as $key => $stmt) {
            if ($stmt instanceof Expression) {
                $stmt = $stmt->expr;
            }

            if (! $this->isAlwaysTruableNode($stmt)) {
                continue;
            }

            /** @var If_ $stmt */
            if (count($stmt->stmts) === 1) {
                $node->stmts[$key] = $stmt->stmts[0];
                continue;
            }

            $haveNodeChanged = true;
            // move all nodes one level up
            array_splice($node->stmts, $key, count($stmt->stmts) - 1, $stmt->stmts);
        }

        if ($haveNodeChanged) {
            return $node;
        }

        return null;
    }

    private function isAlwaysTruableNode(Node $node): bool
    {
        if (! $node instanceof If_) {
            return false;
        }

        // just one if
        if (count($node->elseifs) !== 0) {
            return false;
        }

        // there is some else
        if ($node->else !== null) {
            return false;
        }

        // only property fetch, because of constructor set
        if (! $node->cond instanceof PropertyFetch) {
            return false;
        }

        $propertyFetchType = $this->resolvePropertyFetchType($node->cond);

        return $this->staticTypeAnalyzer->isAlwaysTruableType($propertyFetchType);
    }

    private function resolvePropertyFetchType(PropertyFetch $propertyFetch): Type
    {
        /** @var Class_ $class */
        $class = $propertyFetch->getAttribute(AttributeKey::CLASS_NODE);

        $propertyName = $this->getName($propertyFetch);
        if ($propertyName === null) {
            return new MixedType();
        }

        $property = $class->getProperty($propertyName);
        if ($property === null) {
            return new MixedType();
        }

        // anything but private can be changed from outer scope
        if (! $property->isPrivate()) {
            return new MixedType();
        }

        // set in constructor + changed in class
        $propertyTypeFromConstructor = $this->resolvePropertyTypeAfterConstructor($class, $propertyName);

        $resolvedTypes = [];
        $resolvedTypes[] = $propertyTypeFromConstructor;

        $defaultValue = $property->props[0]->default;
        if ($defaultValue !== null) {
            $resolvedTypes[] = $this->getStaticType($defaultValue);
        }

        $resolveAssignedType = $this->resolveAssignedTypeInStmtsByPropertyName($class->stmts, $propertyName);
        if ($resolveAssignedType !== null) {
            $resolvedTypes[] = $resolveAssignedType;
        }

        return $this->typeFactory->createMixedPassedOrUnionTypeAndKeepConstant($resolvedTypes);
    }

    private function resolvePropertyTypeAfterConstructor(ClassLike $classLike, string $propertyName): Type
    {
        $propertyTypeFromConstructor = null;

        $constructClassMethod = $classLike->getMethod('__construct');
        if ($constructClassMethod !== null) {
            $propertyTypeFromConstructor = $this->resolveAssignedTypeInStmtsByPropertyName(
                (array) $constructClassMethod->stmts,
                $propertyName
            );
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
    private function resolveAssignedTypeInStmtsByPropertyName(array $stmts, string $propertyName): ?Type
    {
        $resolvedTypes = [];

        $this->traverseNodesWithCallable($stmts, function (Node $node) use ($propertyName, &$resolvedTypes): ?int {
            // skip constructor
            if ($node instanceof ClassMethod && $this->isName($node, '__construct')) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }

            if (! $this->isPropertyFetchAssignOfPropertyName($node, $propertyName)) {
                return null;
            }

            $resolvedTypes[] = $this->getStaticType($node->expr);
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
    private function isPropertyFetchAssignOfPropertyName(Node $node, string $propertyName): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        if (! $node->var instanceof PropertyFetch) {
            return false;
        }

        return $this->isName($node->var, $propertyName);
    }
}
