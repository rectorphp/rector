<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeManipulator\ClassMethodAndCallMatcher;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodVendorLockResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector\RemoveDeadRecursiveClassMethodRectorTest
 */
final class RemoveDeadRecursiveClassMethodRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var ClassMethodAndCallMatcher
     */
    private $classMethodAndCallMatcher;

    /**
     * @var ClassMethodVendorLockResolver
     */
    private $classMethodVendorLockResolver;

    public function __construct(
        ClassMethodAndCallMatcher $classMethodAndCallMatcher,
        ClassMethodVendorLockResolver $classMethodVendorLockResolver
    ) {
        $this->classMethodAndCallMatcher = $classMethodAndCallMatcher;
        $this->classMethodVendorLockResolver = $classMethodVendorLockResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove unused public method that only calls itself recursively',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return $this->run();
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
class SomeClass
{
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
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return null;
        }

        if (! $this->containsClassMethodAnyCalls($node)) {
            return null;
        }

        $methodCalls = $this->nodeRepository->findCallsByClassMethod($node);

        // handles remove dead methods rules
        if ($methodCalls === []) {
            return null;
        }

        foreach ($methodCalls as $methodCall) {
            if ($this->shouldSkipCall($node, $methodCall)) {
                return null;
            }
        }

        $this->removeNode($node);

        return null;
    }

    private function containsClassMethodAnyCalls(ClassMethod $classMethod): bool
    {
        return $this->betterNodeFinder->hasInstancesOf($classMethod, [MethodCall::class, StaticCall::class]);
    }

    /**
     * @param StaticCall|MethodCall|ArrayCallable $methodCall
     */
    private function shouldSkipCall(ClassMethod $classMethod, object $methodCall): bool
    {
        if ($this->classMethodVendorLockResolver->isRemovalVendorLocked($classMethod)) {
            return true;
        }

        if (! $methodCall instanceof MethodCall && ! $methodCall instanceof StaticCall) {
            return true;
        }

        /** @var string|null $methodCallMethodName */
        $methodCallMethodName = $methodCall->getAttribute(AttributeKey::METHOD_NAME);
        if ($methodCallMethodName === null) {
            return true;
        }

        if ($methodCall->name instanceof MethodCall) {
            return false;
        }

        // is method called not in itself
        if (! $this->isName($methodCall->name, $methodCallMethodName)) {
            return true;
        }

        // differnt class, probably inheritance
        $methodCallClassName = $methodCall->getAttribute(AttributeKey::CLASS_NAME);
        $classMethodClassName = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
        if ($methodCallClassName !== $classMethodClassName) {
            return true;
        }

        return ! $this->classMethodAndCallMatcher->isMethodLikeCallMatchingClassMethod($methodCall, $classMethod);
    }
}
