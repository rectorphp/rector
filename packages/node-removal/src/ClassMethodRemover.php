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
    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    /**
     * @var NodeRemover
     */
    private $nodeRemover;

    /**
     * @var LivingCodeManipulator
     */
    private $livingCodeManipulator;

    public function __construct(
        NodeRepository $nodeRepository,
        NodeRemover $nodeRemover,
        LivingCodeManipulator $livingCodeManipulator
    ) {
        $this->nodeRepository = $nodeRepository;
        $this->nodeRemover = $nodeRemover;
        $this->livingCodeManipulator = $livingCodeManipulator;
    }

    public function removeClassMethodAndUsages(ClassMethod $classMethod): void
    {
        $this->nodeRemover->removeNode($classMethod);

        $calls = $this->nodeRepository->findCallsByClassMethod($classMethod);
        foreach ($calls as $classMethodCall) {
            if ($classMethodCall instanceof ArrayCallable) {
                continue;
            }

            $this->removeMethodCall($classMethodCall);
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
