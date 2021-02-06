<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\NodeAnalyzer\ClassMethodExternalCallNodeAnalyzer;
use ReflectionClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Privatization\Tests\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector\MakeOnlyUsedByChildrenProtectedRectorTest
 */
final class MakeOnlyUsedByChildrenProtectedRector extends AbstractRector
{
    /**
     * @var ClassMethodExternalCallNodeAnalyzer
     */
    private $classMethodExternalCallNodeAnalyzer;

    /**
     * @var FamilyRelationsAnalyzer
     */
    private $familyRelationsAnalyzer;

    public function __construct(
        ClassMethodExternalCallNodeAnalyzer $classMethodExternalCallNodeAnalyzer,
        FamilyRelationsAnalyzer $familyRelationsAnalyzer
    ) {
        $this->classMethodExternalCallNodeAnalyzer = $classMethodExternalCallNodeAnalyzer;
        $this->familyRelationsAnalyzer = $familyRelationsAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Make public class method protected, if only used by its children',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
abstract class AbstractSomeClass
{
    public function run()
    {
    }
}

class ChildClass extends AbstractSomeClass
{
    public function go()
    {
        $this->run();
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
abstract class AbstractSomeClass
{
    protected function run()
    {
    }
}

class ChildClass extends AbstractSomeClass
{
    public function go()
    {
        $this->run();
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $currentClass = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $currentClass instanceof Class_) {
            return null;
        }

        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return null;
        }

        if (! $node->isPublic()) {
            return null;
        }

        $externalCalls = $this->classMethodExternalCallNodeAnalyzer->getExternalCalls($node);
        if ($externalCalls === []) {
            return null;
        }

        foreach ($externalCalls as $call) {
            $class = $call->getAttribute(AttributeKey::CLASS_NODE);
            if (! $class instanceof Class_) {
                return null;
            }

            if (! $this->isObjectType($class, $className)) {
                return null;
            }
        }

        $methodName = $this->getName($node);
        if ($this->isOverriddenInChildClass($className, $methodName)) {
            return null;
        }

        if ($currentClass->extends instanceof FullyQualified) {
            return null;
        }

        $this->visibilityManipulator->makeProtected($node);
        return $node;
    }

    private function isOverriddenInChildClass(string $className, string $methodName): bool
    {
        $childrenClassNames = $this->familyRelationsAnalyzer->getChildrenOfClass($className);
        foreach ($childrenClassNames as $childrenClassName) {
            $reflectionClass = new ReflectionClass($childrenClassName);
            if (method_exists($childrenClassName, $methodName) && $reflectionClass->getMethod(
                $methodName
            )->class === $childrenClassName) {
                return true;
            }
        }

        return false;
    }
}
