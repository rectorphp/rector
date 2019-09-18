<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\DeadCode\Analyzer\SetterOnlyMethodAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Manipulator\AssignManipulator;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 *
 * @see \Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\RemoveSetterOnlyPropertyAndMethodCallRectorTest
 */
final class RemoveSetterOnlyPropertyAndMethodCallRector extends AbstractRector
{
    /**
     * @var SetterOnlyMethodAnalyzer
     */
    private $setterOnlyMethodAnalyzer;

    /**
     * @var AssignManipulator
     */
    private $assignManipulator;

    public function __construct(
        SetterOnlyMethodAnalyzer $setterOnlyMethodAnalyzer,
        AssignManipulator $assignManipulator
    ) {
        $this->setterOnlyMethodAnalyzer = $setterOnlyMethodAnalyzer;
        $this->assignManipulator = $assignManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes method that set values that are never used', [
            new CodeSample(
                <<<'PHP'
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
PHP
                ,
                <<<'PHP'
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
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class, MethodCall::class, ClassMethod::class, Assign::class];
    }

    /**
     * @param Property|MethodCall|ClassMethod|Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        $setterOnlyPropertiesAndMethods = $this->resolveSetterOnlyPropertiesAndMethodsForClass($node);
        if ($setterOnlyPropertiesAndMethods === null) {
            return null;
        }

        // remove method calls
        if ($node instanceof MethodCall) {
            if ($this->isNames($node->name, $setterOnlyPropertiesAndMethods['methods'] ?? [])) {
                $this->removeNode($node);
            }

            return null;
        }

        $this->processClassStmts($node, $setterOnlyPropertiesAndMethods);

        return null;
    }

    /**
     * @param Property|ClassMethod|MethodCall|Assign $node
     * @return string[][]|null
     */
    private function resolveSetterOnlyPropertiesAndMethodsForClass(Node $node): ?array
    {
        $setterOnlyPropertiesAndMethodsByType = $this->setterOnlyMethodAnalyzer->provideSetterOnlyPropertiesAndMethodsByType();

        foreach ($setterOnlyPropertiesAndMethodsByType as $type => $setterOnlyPropertiesAndMethods) {
            if ($node instanceof MethodCall) {
                if (! $this->isObjectType($node->var, $type)) {
                    continue;
                }

                return $setterOnlyPropertiesAndMethods;
            }

            $className = $node->getAttribute(AttributeKey::CLASS_NAME);
            if ($className === $type) {
                return $setterOnlyPropertiesAndMethods;
            }
        }

        return null;
    }

    /**
     * @param Property|Assign|ClassMethod $node
     * @param string[][] $setterOnlyPropertiesAndMethods
     */
    private function processClassStmts(Node $node, array $setterOnlyPropertiesAndMethods): void
    {
        $propertyNames = $setterOnlyPropertiesAndMethods['properties'] ?? [];
        $methodNames = $setterOnlyPropertiesAndMethods['methods'] ?? [];

        // 1. remove class properties
        if ($node instanceof Property) {
            if ($this->isNames($node, $propertyNames)) {
                $this->removeNode($node);
            }
        }

        // 2. remove class inner assigns
        if ($this->assignManipulator->isLocalPropertyAssign($node)) {
            /** @var Assign $node */
            $propertyFetch = $node->var;
            /** @var PropertyFetch $propertyFetch */
            if ($this->isNames($propertyFetch->name, $propertyNames)) {
                $this->removeNode($node);
            }
        }

        // 3. remove class methods
        if ($node instanceof ClassMethod) {
            if ($this->isNames($node, $methodNames)) {
                $this->removeNode($node);
            }
        }
    }
}
