<?php

declare(strict_types=1);

namespace Rector\__Package__\Rector\__Category__;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
__Resources__
 * @see \Rector\__Package__\Tests\Rector\__Category__\__Name__\__Name__Test
 */
final class __Name__ extends AbstractRector
{
    public function getRuleDefinition(): \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('__Description__', [
            new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(
                __CodeBeforeExample__
                ,
                __CodeAfterExample__
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
}
