<?php

declare(strict_types=1);

namespace Rector\__Package__\Rector\__Category__;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
_Source_
 * @see \Rector\__Package__\Tests\Rector\__Category__\__Name__\__Name__Test
 */
final class __Name__ extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('__Description__', [
            new CodeSample(
                __CodeBeforeExample__,
                __CodeAfterExample__
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
