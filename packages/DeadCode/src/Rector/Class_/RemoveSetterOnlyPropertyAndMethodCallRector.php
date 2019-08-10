<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\DeadCode\Analyzer\SetterOnlyMethodAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @sponsor Thanks https://spaceflow.io/ for sponsoring this rule - visit them on https://github.com/SpaceFlow-app
 * @see \Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\RemoveSetterOnlyPropertyAndMethodCallRectorTest
 */
final class RemoveSetterOnlyPropertyAndMethodCallRector extends AbstractRector
{
    /**
     * @var SetterOnlyMethodAnalyzer
     */
    private $setterOnlyMethodAnalyzer;

    public function __construct(SetterOnlyMethodAnalyzer $setterOnlyMethodAnalyzer)
    {
        $this->setterOnlyMethodAnalyzer = $setterOnlyMethodAnalyzer;
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
        return [Property::class, MethodCall::class, ClassMethod::class];
    }

    /**
     * @param Property|MethodCall|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $setterOnlyPropertiesAndMethods = $this->resolveSetterOnlyPropertiesAndMethodsForClass($node);
        if ($setterOnlyPropertiesAndMethods === null) {
            return null;
        }

        // 1. remove class properties
        if ($node instanceof Property) {
            if ($this->isNames($node, $setterOnlyPropertiesAndMethods['properties'] ?? [])) {
                $this->removeNode($node);
            }
        }

        // 2. remove class methods
        if ($node instanceof ClassMethod) {
            if ($this->isNames($node, $setterOnlyPropertiesAndMethods['methods'] ?? [])) {
                $this->removeNode($node);
            }
        }

        // 3. remove method calls
        if ($node instanceof MethodCall) {
            if ($this->isNames($node->name, $setterOnlyPropertiesAndMethods['methods'] ?? [])) {
                $this->removeNode($node);
            }
        }

        return null;
    }

    /**
     * @param Property|ClassMethod|MethodCall $node
     * @return string[][]][]|null
     */
    private function resolveSetterOnlyPropertiesAndMethodsForClass(Node $node): ?array
    {
        if ($node instanceof Property || $node instanceof ClassMethod) {
            $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        } elseif ($node instanceof MethodCall) {
            $className = $this->getTypes($node->var)[0] ?? null;
            if ($className === null) {
                return null;
            }
        }

        $setterOnlyPropertiesAndMethodsByType = $this->setterOnlyMethodAnalyzer->provideSetterOnlyPropertiesAndMethodsByType();

        return $setterOnlyPropertiesAndMethodsByType[$className] ?? null;
    }
}
