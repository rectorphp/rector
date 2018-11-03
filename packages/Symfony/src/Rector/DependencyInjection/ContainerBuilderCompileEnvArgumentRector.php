<?php declare(strict_types=1);

namespace Rector\Symfony\Rector\DependencyInjection;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ContainerBuilderCompileEnvArgumentRector extends AbstractRector
{
    /**
     * @var string
     */
    private $containerBuilderClass;

    public function __construct(
        string $containerBuilderClass = 'Symfony\Component\DependencyInjection\ContainerBuilder'
    ) {
        $this->containerBuilderClass = $containerBuilderClass;
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
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isType($node, $this->containerBuilderClass)) {
            return null;
        }

        if (! $this->isName($node, 'compile')) {
            return null;
        }

        if (count($node->args) === 1) {
            return null;
        }

        $node->args = $this->createArgs([$this->createTrue()]);

        return $node;
    }
}
