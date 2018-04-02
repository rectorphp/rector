<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Symfony\DependencyInjection;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;

final class ContainerBuilderCompileEnvArgumentRector extends AbstractRector
{
    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, NodeFactory $nodeFactory)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->nodeFactory = $nodeFactory;
    }

    public function isCandidate(Node $node): bool
    {
        if ($this->methodCallAnalyzer->isTypeAndMethods(
            $node,
            'Symfony\Component\DependencyInjection\ContainerBuilder',
            ['compile']
        ) === false) {
            return false;
        }

        /** @var MethodCall $methodCallNode */
        $methodCallNode = $node;

        $arguments = $methodCallNode->args;

        // already has an argument
        return count($arguments) !== 1;
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        $methodCallNode->args = $this->nodeFactory->createArgs([true]);

        return $methodCallNode;
    }
}
