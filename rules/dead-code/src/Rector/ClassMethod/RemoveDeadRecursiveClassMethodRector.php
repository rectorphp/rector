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
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DeadCode\NodeManipulator\ClassMethodAndCallMatcher;
use Rector\NodeCollector\NodeFinder\MethodCallParsedNodesFinder;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodVendorLockResolver;

/**
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveDeadRecursiveClassMethodRector\RemoveDeadRecursiveClassMethodRectorTest
 */
final class RemoveDeadRecursiveClassMethodRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var MethodCallParsedNodesFinder
     */
    private $methodCallParsedNodesFinder;

    /**
     * @var ClassMethodAndCallMatcher
     */
    private $classMethodAndCallMatcher;

    /**
     * @var ClassMethodVendorLockResolver
     */
    private $classMethodVendorLockResolver;

    public function __construct(
        MethodCallParsedNodesFinder $methodCallParsedNodesFinder,
        ClassMethodAndCallMatcher $classMethodAndCallMatcher,
        ClassMethodVendorLockResolver $classMethodVendorLockResolver
    ) {
        $this->methodCallParsedNodesFinder = $methodCallParsedNodesFinder;
        $this->classMethodAndCallMatcher = $classMethodAndCallMatcher;
        $this->classMethodVendorLockResolver = $classMethodVendorLockResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused public method that only calls itself recursively', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function run()
    {
        return $this->run();
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return null;
        }

        if (! $this->containsClassMethodAnyCalls($node)) {
            return null;
        }

        $methodCalls = $this->methodCallParsedNodesFinder->findByClassMethod($node);

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

        /** @var string $methodCallMethodName */
        $methodCallMethodName = $methodCall->getAttribute(AttributeKey::METHOD_NAME);

        // is method called not in itself
        if (! $this->isName($methodCall->name, $methodCallMethodName)) {
            return true;
        }

        // differnt class, probably inheritance
        if ($methodCall->getAttribute(AttributeKey::CLASS_NAME) !== $classMethod->getAttribute(
            AttributeKey::CLASS_NAME
        )) {
            return true;
        }

        return ! $this->classMethodAndCallMatcher->isMethodLikeCallMatchingClassMethod($methodCall, $classMethod);
    }

    private function containsClassMethodAnyCalls(ClassMethod $classMethod): bool
    {
        return $this->betterNodeFinder->hasInstancesOf($classMethod, [MethodCall::class, StaticCall::class]);
    }
}
