<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Caching\Contract\Rector\ZeroCacheRectorInterface;
use Rector\CodingStyle\ValueObject\ObjectMagicMethods;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DeadCode\Tests\Rector\ClassMethod\RemoveUnusedPublicMethodRector\RemoveUnusedPublicMethodRectorTest
 */
final class RemoveUnusedPublicMethodRector extends AbstractRector implements ZeroCacheRectorInterface
{
    /**
     * @var MethodCall[]|StaticCall[]|ArrayCallable[]
     */
    private $calls = [];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove unused public method',
            [
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
        if ($this->isOpenSourceProjectType()) {
            return null;
        }

        if (! $node->isPublic()) {
            return null;
        }

        if ($this->isNames($node, ObjectMagicMethods::METHOD_NAMES)) {
            return null;
        }

        $calls = $this->nodeRepository->findCallsByClassMethod($node);
        if ($calls !== []) {
            $this->calls = array_merge($this->calls, $calls);
            return null;
        }

        /** @var MethodCall[] $calls */
        $calls = $this->calls;
        foreach ($calls as $call) {
            $classMethod = $this->betterNodeFinder->findParentType($call, ClassMethod::class);

            if ($this->areNodesEqual($classMethod, $node)) {
                return null;
            }
        }

        $this->removeNode($node);
        return $node;
    }
}
