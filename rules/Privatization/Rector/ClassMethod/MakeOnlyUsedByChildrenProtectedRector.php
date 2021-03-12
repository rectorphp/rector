<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\NodeAnalyzer\ClassMethodExternalCallNodeAnalyzer;
use Rector\Privatization\VisibilityGuard\ChildClassMethodOverrideGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\Privatization\Rector\ClassMethod\MakeOnlyUsedByChildrenProtectedRector\MakeOnlyUsedByChildrenProtectedRectorTest
 */
final class MakeOnlyUsedByChildrenProtectedRector extends AbstractRector
{
    /**
     * @var ClassMethodExternalCallNodeAnalyzer
     */
    private $classMethodExternalCallNodeAnalyzer;

    /**
     * @var ChildClassMethodOverrideGuard
     */
    private $childClassMethodOverrideGuard;

    public function __construct(
        ClassMethodExternalCallNodeAnalyzer $classMethodExternalCallNodeAnalyzer,
        ChildClassMethodOverrideGuard $childClassMethodOverrideGuard
    ) {
        $this->classMethodExternalCallNodeAnalyzer = $classMethodExternalCallNodeAnalyzer;
        $this->childClassMethodOverrideGuard = $childClassMethodOverrideGuard;
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
     * @return array<class-string<Node>>
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
        if ($this->shouldSkip($node)) {
            return null;
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (! $scope instanceof Scope) {
            throw new ShouldNotHappenException();
        }

        $externalCalls = $this->classMethodExternalCallNodeAnalyzer->getExternalCalls($node);
        if ($externalCalls === []) {
            return null;
        }

        /** @var string $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);

        foreach ($externalCalls as $externalCall) {
            $class = $externalCall->getAttribute(AttributeKey::CLASS_NODE);
            if (! $class instanceof Class_) {
                return null;
            }

            if (! $this->isObjectType($class, new ObjectType($className))) {
                return null;
            }
        }

        $methodName = $this->getName($node);
        if ($this->childClassMethodOverrideGuard->isOverriddenInChildClass($scope, $methodName)) {
            return null;
        }

        $this->visibilityManipulator->makeProtected($node);
        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        $currentClass = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $currentClass instanceof Class_) {
            return true;
        }

        if ($currentClass->isFinal()) {
            return true;
        }

        if ($currentClass->extends instanceof FullyQualified) {
            return true;
        }

        if ($currentClass->isAbstract() && $this->isOpenSourceProjectType()) {
            return true;
        }

        $className = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if ($className === null) {
            return true;
        }

        return ! $classMethod->isPublic();
    }
}
