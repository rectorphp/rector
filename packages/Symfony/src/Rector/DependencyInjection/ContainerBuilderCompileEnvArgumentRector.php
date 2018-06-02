<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\DependencyInjection;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\NodeFactory;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

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

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Turns old default value to parameter in ContinerBuilder->build() method in DI in Symfony',
            [
                new CodeSample(
                    '$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder(); $containerBuilder->compile();',
                    '$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder(); $containerBuilder->compile(true);'
                ),
            ]
        );
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

        // already has an argument
        return count($methodCallNode->args) !== 1;
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
