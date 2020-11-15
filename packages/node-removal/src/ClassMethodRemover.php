<?php

declare(strict_types=1);

namespace Rector\NodeRemoval;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\DeadCode\NodeManipulator\LivingCodeManipulator;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\Collector\NodesToAddCollector;

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

    /**
     * @var NodesToAddCollector
     */
    private $nodesToAddCollector;

    public function __construct(
        NodeRepository $nodeRepository,
        NodeRemover $nodeRemover,
        LivingCodeManipulator $livingCodeManipulator,
        NodesToAddCollector $nodesToAddCollector
    ) {
        $this->nodeRepository = $nodeRepository;
        $this->nodeRemover = $nodeRemover;
        $this->livingCodeManipulator = $livingCodeManipulator;
        $this->nodesToAddCollector = $nodesToAddCollector;
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
            $this->addLivingCodeBeforeNode($arg->value, $currentStatement);
        }

        $this->nodeRemover->removeNode($node);
    }

    private function addLivingCodeBeforeNode(Expr $expr, Node $addBeforeThisNode): void
    {
        foreach ($this->livingCodeManipulator->keepLivingCodeFromExpr($expr) as $expr) {
            $this->nodesToAddCollector->addNodeBeforeNode(new Expression($expr), $addBeforeThisNode);
        }
    }
}
