<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\DependencyInjection;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Node\NodeFactory;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ContainerBuilderCompileEnvArgumentRector extends AbstractRector
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(NodeFactory $nodeFactory)
    {
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

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $methodCallNode
     */
    public function refactor(Node $methodCallNode): ?Node
    {
        if (! $this->isType($methodCallNode, 'Symfony\Component\DependencyInjection\ContainerBuilder')) {
            return null;
        }

        if (! $this->isName($methodCallNode, 'compile')) {
            return null;
        }

        if ((count($methodCallNode->args) !== 1) === false) {
            return null;
        }

        $methodCallNode->args = $this->nodeFactory->createArgs([true]);

        return $methodCallNode;
    }
}
