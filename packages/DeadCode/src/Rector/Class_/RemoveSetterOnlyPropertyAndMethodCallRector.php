<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeContainer\ParsedNodesByType;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\RemoveSetterOnlyPropertyAndMethodCallRectorTest
 */
final class RemoveSetterOnlyPropertyAndMethodCallRector extends AbstractRector
{
    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var ParsedNodesByType
     */
    private $parsedNodesByType;

    /**
     * @var string[]
     */
    private $methodCallNamesToBeRemoved = [];

    public function __construct(ClassManipulator $classManipulator, ParsedNodesByType $parsedNodesByType)
    {
        $this->classManipulator = $classManipulator;
        $this->parsedNodesByType = $parsedNodesByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes method that set values that are never used', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    private $name;

    public function setName($name)
    {
        $this->name = $name;
    }
}

class ActiveOnlySetter
{
    public function run()
    {
        $someClass = new SomeClass();
        $someClass->setName('Tom');
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
}

class ActiveOnlySetter
{
    public function run()
    {
        $someClass = new SomeClass();
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
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $this->methodCallNamesToBeRemoved = [];

        // 1. get assign only private properties
        $assignOnlyPrivatePropertyNames = $this->classManipulator->getAssignOnlyPrivatePropertyNames($node);
        $this->classManipulator->removeProperties($node, $assignOnlyPrivatePropertyNames);

        // 2. remove assigns + class methods with only setter assign
        $this->removePropertyAssigns($node, $assignOnlyPrivatePropertyNames);

        // 3. remove setter method calls
        $this->removeSetterMethodCalls($node);

        return $node;
    }

    /**
     * @param string[] $assignOnlyPrivatePropertyNames
     */
    private function removePropertyAssigns(Class_ $class, array $assignOnlyPrivatePropertyNames): void
    {
        $this->traverseNodesWithCallable($class, function (Node $node) use ($assignOnlyPrivatePropertyNames): void {
            if ($this->isClassMethodWithSinglePropertyAssignOfNames($node, $assignOnlyPrivatePropertyNames)) {
                /** @var string $classMethodName */
                $classMethodName = $this->getName($node);
                $this->methodCallNamesToBeRemoved[] = $classMethodName;

                $this->removeNode($node);
                return;
            }

            if ($this->isPropertyAssignWithPropertyNames($node, $assignOnlyPrivatePropertyNames)) {
                $this->removeNode($node);
            }
        });
    }

    private function removeSetterMethodCalls(Node $node): void
    {
        /** @var string $className */
        $className = $this->getName($node);
        $methodCallsByMethodName = $this->parsedNodesByType->findMethodCallsOnClass($className);

        /** @var string $methodName */
        foreach ($methodCallsByMethodName as $methodName => $classMethodCalls) {
            if (! in_array($methodName, $this->methodCallNamesToBeRemoved, true)) {
                continue;
            }

            foreach ($classMethodCalls as $classMethodCall) {
                $this->removeNode($classMethodCall);
            }
        }
    }

    /**
     * Looks for:
     *
     * public function <someMethod>($value)
     * {
     *     $this->value = $value
     * }
     *
     * @param string[] $propertyNames
     */
    private function isClassMethodWithSinglePropertyAssignOfNames(Node $node, array $propertyNames): bool
    {
        if (! $node instanceof ClassMethod) {
            return false;
        }

        if (count((array) $node->stmts) !== 1) {
            return false;
        }

        if (! $node->stmts[0] instanceof Expression) {
            return false;
        }

        /** @var Expression $onlyExpression */
        $onlyExpression = $node->stmts[0];

        $onlyStmt = $onlyExpression->expr;

        return $this->isPropertyAssignWithPropertyNames($onlyStmt, $propertyNames);
    }

    /**
     * Is: "$this->value = <$value>"
     *
     * @param string[] $propertyNames
     */
    private function isPropertyAssignWithPropertyNames(Node $node, array $propertyNames): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        if (! $node->var instanceof PropertyFetch) {
            return false;
        }

        $propertyFetch = $node->var;
        if (! $this->isName($propertyFetch->var, 'this')) {
            return false;
        }

        return $this->isNames($propertyFetch->name, $propertyNames);
    }
}
