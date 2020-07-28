<?php

declare(strict_types=1);

namespace Rector\__Package__\Rector\__Category__;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
__Source__
 * @see \Rector\__Package__\Tests\Rector\__Category__\__Name__\__Name__Test
 */
final class __Name__ extends AbstractRector
{
    __ConfigurationProperty__

    __ConfigurationConstructor__

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
        return __NodeTypes_Php__;
    }

    /**
     * @param __NodeTypes_Doc__ $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }
}
