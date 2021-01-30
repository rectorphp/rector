<?php

declare(strict_types=1);

namespace Rector\RemovingStatic\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\RemovingStatic\Tests\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector\LocallyCalledStaticMethodToNonStaticRectorTest
 */
final class LocallyCalledStaticMethodToNonStaticRector extends AbstractRector
{
    /**
     * @var ClassMethodVisibilityGuard
     */
    private $classMethodVisibilityGuard;

    public function __construct(ClassMethodVisibilityGuard $classMethodVisibilityGuard)
    {
        $this->classMethodVisibilityGuard = $classMethodVisibilityGuard;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change static method and local-only calls to non-static',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        self::someStatic();
    }

    private static function someStatic()
    {
    }
}
CODE_SAMPLE

                    ,
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $this->someStatic();
    }

    private function someStatic()
    {
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
        return [ClassMethod::class, StaticCall::class];
    }

    /**
     * @param ClassMethod|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassMethod) {
            return $this->refactorClassMethod($node);
        }

        return $this->refactorStaticCall($node);
    }

    private function refactorClassMethod(ClassMethod $classMethod): ?ClassMethod
    {
        if (! $classMethod->isStatic()) {
            return null;
        }

        if (! $this->isClassMethodWithOnlyLocalStaticCalls($classMethod)) {
            return null;
        }

        if ($this->classMethodVisibilityGuard->isClassMethodVisibilityGuardedByParent($classMethod)) {
            return null;
        }

        // change static calls to non-static ones, but only if in non-static method!!!
        $this->visibilityManipulator->makeNonStatic($classMethod);

        return $classMethod;
    }

    private function refactorStaticCall(StaticCall $staticCall): ?MethodCall
    {
        $classMethod = $this->nodeRepository->findClassMethodByStaticCall($staticCall);
        if (! $classMethod instanceof ClassMethod) {
            return null;
        }

        // is static call in the same as class method
        if (! $this->haveSharedClass($classMethod, [$staticCall])) {
            return null;
        }

        if ($this->isInStaticClassMethod($staticCall)) {
            return null;
        }

        $thisVariable = new Variable('this');
        return new MethodCall($thisVariable, $staticCall->name, $staticCall->args);
    }

    private function isClassMethodWithOnlyLocalStaticCalls(ClassMethod $classMethod): bool
    {
        $staticCalls = $this->nodeRepository->findStaticCallsByClassMethod($classMethod);

        // get static staticCalls
        return $this->haveSharedClass($classMethod, $staticCalls);
    }

    /**
     * @param Node[] $nodes
     */
    private function haveSharedClass(Node $mainNode, array $nodes): bool
    {
        $mainNodeClass = $mainNode->getAttribute(AttributeKey::CLASS_NAME);
        foreach ($nodes as $node) {
            $nodeClass = $node->getAttribute(AttributeKey::CLASS_NAME);
            if ($mainNodeClass !== $nodeClass) {
                return false;
            }
        }

        return true;
    }

    private function isInStaticClassMethod(StaticCall $staticCall): bool
    {
        $locationClassMethod = $staticCall->getAttribute(AttributeKey::METHOD_NODE);
        if (! $locationClassMethod instanceof ClassMethod) {
            return false;
        }

        return $locationClassMethod->isStatic();
    }
}
