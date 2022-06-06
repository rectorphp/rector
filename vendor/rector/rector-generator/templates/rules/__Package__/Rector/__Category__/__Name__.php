<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\__Package__\Rector\__Category__;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
__Resources__
* @see \Rector\Tests\__Package__\Rector\__Category__\__Name__\__Name__Test
*/
final class __Name__ extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('__Description__', [new CodeSample(__CodeBeforeExample__, __CodeAfterExample__)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return __NodeTypesPhp__;
    }
    /**
     * @param __NodeTypesDoc__ $node
     */
    public function refactor(Node $node) : ?Node
    {
        // change the node
        return $node;
    }
}
