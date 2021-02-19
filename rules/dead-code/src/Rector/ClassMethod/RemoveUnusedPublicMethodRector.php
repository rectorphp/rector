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
use Rector\DeadCode\NodeAnalyzer\DataProviderMethodNamesResolver;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnVendorLockResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPublicMethodRector\RemoveUnusedPublicMethodRectorTest
 */
final class RemoveUnusedPublicMethodRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var DataProviderMethodNamesResolver
     */
    private $dataProviderMethodNamesResolver;

    /**
     * @var MethodCall[]|StaticCall[]|ArrayCallable[]
     */
    private $calls = [];

    /**
     * @var ClassMethodReturnVendorLockResolver
     */
    private $classMethodReturnVendorLockResolver;

    public function __construct(
        DataProviderMethodNamesResolver $dataProviderMethodNamesResolver,
        ClassMethodReturnVendorLockResolver $classMethodReturnVendorLockResolver
    ) {
        $this->dataProviderMethodNamesResolver = $dataProviderMethodNamesResolver;
        $this->classMethodReturnVendorLockResolver = $classMethodReturnVendorLockResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused public method', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function unusedpublicMethod()
    {
        // ...
    }

    public function execute()
    {
        // ...
    }

    public function run()
    {
        $obj = new self;
        $obj->execute();
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function execute()
    {
        // ...
    }

    public function run()
    {
        $obj = new self;
        $obj->execute();
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

        $calls = $this->nodeRepository->findCallsByClassMethod($node);
        if ($calls !== []) {
            $this->calls = array_merge($this->calls, $calls);
            return null;
        }

        if ($this->isRecursionCallClassMethod($node)) {
            return null;
        }

        $this->removeNode($node);
        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        $class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $class instanceof Class_) {
            return true;
        }

        if ($this->isOpenSourceProjectType()) {
            return true;
        }

        if (! $classMethod->isPublic()) {
            return true;
        }

        if ($this->classMethodReturnVendorLockResolver->isVendorLocked($classMethod)) {
            return true;
        }

        if ($classMethod->isMagic()) {
            return true;
        }

        if ($this->isNames($classMethod, ['test', 'test*'])) {
            return true;
        }

        $class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);

        $phpunitDataProviderMethodNames = $this->dataProviderMethodNamesResolver->resolveFromClass($class);
        return $this->isNames($classMethod, $phpunitDataProviderMethodNames);
    }

    private function isRecursionCallClassMethod(ClassMethod $currentClassMethod): bool
    {
        /** @var MethodCall[] $calls */
        $calls = $this->calls;

        foreach ($calls as $call) {
            $parentClassMethod = $call->getAttribute(AttributeKey::METHOD_NODE);
            if (! $parentClassMethod) {
                continue;
            }

<<<<<<< HEAD
            if ($this->nodeComparator->areNodesEqual($parentClassMethod, $currentClassMethod)) {
                return true;
=======
            if ($this->nodeComparator->areNodesEqual($classMethod, $node)) {
                return null;
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
            }
        }

        return false;
    }
}
