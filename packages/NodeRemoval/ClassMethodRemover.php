<?php

declare(strict_types=1);

namespace Rector\NodeRemoval;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\DeadCode\NodeManipulator\LivingCodeManipulator;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ClassMethodRemover
{
    public function __construct(
        private NodeRepository $nodeRepository,
        private NodeRemover $nodeRemover,
        private LivingCodeManipulator $livingCodeManipulator
    ) {
    }

    public function removeClassMethodAndUsages(ClassMethod $classMethod): void
    {
        $this->nodeRemover->removeNode($classMethod);

        $calls = $this->nodeRepository->findCallsByClassMethod($classMethod);
        foreach ($calls as $call) {
            if ($call instanceof ArrayCallable) {
                continue;
            }

            $this->removeMethodCall($call);
        }
    }

    /**
     * @param MethodCall|StaticCall $node
     */
    private function removeMethodCall(Node $node): void
    {
        $currentStatement = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
        foreach ($node->args as $arg) {
            $this->livingCodeManipulator->addLivingCodeBeforeNode($arg->value, $currentStatement);
        }

        $this->nodeRemover->removeNode($node);
    }
}
