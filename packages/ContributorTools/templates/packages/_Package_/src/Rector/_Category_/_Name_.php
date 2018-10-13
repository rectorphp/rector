<?php declare(strict_types=1);

namespace Rector\_Package_\Rector\_Category_;

use PhpParser\Node;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class _Name_ extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('_Description_', [
            new CodeSample(
                '_CodeBefore_',
                '_CodeAfter_'
            )
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [_NodeTypes_Php_];
    }

    /**
     * @param _NodeTypes_Doc_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }
}
