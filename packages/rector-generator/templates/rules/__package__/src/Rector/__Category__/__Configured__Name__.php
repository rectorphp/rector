<?php

declare(strict_types=1);

namespace Rector\__Package__\Rector\__Category__;

use PhpParser\Node;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
__Resources__
 * @see \Rector\__Package__\Tests\Rector\__Category__\__Name__\__Name__Test
 */
final class __Name__ extends AbstractRector implements ConfigurableRectorInterface
{
    __ConfigurationConstants__

    __ConfigurationProperties__

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('__Description__', [
            new ConfiguredCodeSample(
                __CodeBeforeExample__,
                __CodeAfterExample__,
                __RuleConfiguration__
            )
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return __NodeTypesPhp__;
    }

    /**
     * @param __NodeTypesDoc__ $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }

    __ConfigureClassMethod__
}
