<?php

declare(strict_types=1);

namespace Rector\Privatization\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\FamilyTree\Reflection\FamilyRelationsAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\NodeAnalyzer\ClassMethodExternalCallNodeAnalyzer;
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

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
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
        if ($this->isOverriddenInChildClass($classReflection, $methodName)) {
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

    private function isOverriddenInChildClass(ClassReflection $classReflection, string $methodName): bool
    {
        $childrenClassReflection = $this->familyRelationsAnalyzer->getChildrenOfClassReflection($classReflection);

        foreach ($childrenClassReflection as $singleChildrenClassReflection) {
            $singleChildrenClassReflectionHasMethod = $singleChildrenClassReflection->hasMethod($methodName);
            if (! $singleChildrenClassReflectionHasMethod) {
                continue;
            }

            $methodReflection = $singleChildrenClassReflection->getNativeMethod($methodName);
            $methodDeclaringClass = $methodReflection->getDeclaringClass();

            if ($methodDeclaringClass->getName() === $singleChildrenClassReflection->getName()) {
                return true;
            }
        }

        return false;
    }
}
