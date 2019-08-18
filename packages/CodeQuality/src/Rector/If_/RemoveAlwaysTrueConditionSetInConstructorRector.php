<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\StaticTypeAnalyzer;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://phpstan.org/r/e909844a-084e-427e-92ac-fed3c2aeabab
 * @see \Rector\CodeQuality\Tests\Rector\If_\RemoveAlwaysTrueConditionSetInConstructorRector\RemoveAlwaysTrueConditionSetInConstructorRectorTest
 */
final class RemoveAlwaysTrueConditionSetInConstructorRector extends AbstractRector
{
    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var StaticTypeAnalyzer
     */
    private $staticTypeAnalyzer;

    public function __construct(ClassManipulator $classManipulator, StaticTypeAnalyzer $staticTypeAnalyzer)
    {
        $this->classManipulator = $classManipulator;
        $this->staticTypeAnalyzer = $staticTypeAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('If conditions is always true, perform the content right away', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
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
        if ($node->stmts === null) {
            return null;
        }

        foreach ($node->stmts as $key => $stmt) {
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

            // move all nodes one level up
            array_splice($node->stmts, $key, count($stmt->stmts) - 1, $stmt->stmts);
        }

        return $node;
    }

    /**
     * @return Type[]
     */
    private function resolvePropertyFetchTypes(PropertyFetch $propertyFetch): array
    {
        /** @var Class_ $class */
        $class = $propertyFetch->getAttribute(AttributeKey::CLASS_NODE);

        $propertyName = $this->getName($propertyFetch);
        if ($propertyName === null) {
            return [];
        }

        $property = $this->classManipulator->getProperty($class, $propertyName);
        if ($property === null) {
            return [];
        }

        // anything but private can be changed from outer scope
        if (! $property->isPrivate()) {
            return [];
        }

        // set in constructor + changed in class
        $constructClassMethod = $class->getMethod('__construct');
        if ($constructClassMethod === null) {
            return [];
        }

        $resolvedTypes = [];

        $this->traverseNodesWithCallable($class->stmts, function (Node $node) use (
            $propertyName,
            &$resolvedTypes
        ) {
            if (! $node instanceof Assign) {
                return null;
            }

            if (! $node->var instanceof PropertyFetch) {
                return null;
            }

            if (! $this->isName($node->var, $propertyName)) {
                return null;
            }

            $staticType = $this->getStaticType($node->expr);
            if ($staticType !== null) {
                $resolvedTypes[] = $staticType;
            }

            return null;
        });

        return $resolvedTypes;
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

        $propertyFetchTypes = $this->resolvePropertyFetchTypes($node->cond);

        return $this->staticTypeAnalyzer->areTypesAlwaysTruable($propertyFetchTypes);
    }
}
