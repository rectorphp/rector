<?php

declare(strict_types=1);

namespace Rector\Php52\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Php52\Tests\Rector\Property\VarToPublicPropertyRector\VarToPublicPropertyRectorTest
 */
final class VarToPublicPropertyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused private method', [
            new CodeSample(
                <<<'PHP'
final class SomeController
{
    var $name = 'Tom';
}
PHP
                ,
                <<<'PHP'
final class SomeController
{
    public $name = 'Tom';
}
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        // explicitly public
        if ($node->flags !== 0) {
            return null;
        }

        $this->makePublic($node);

        return $node;
    }
}
