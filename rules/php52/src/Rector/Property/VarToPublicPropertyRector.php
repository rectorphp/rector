<?php

declare(strict_types=1);

namespace Rector\Php52\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Php52\Tests\Rector\Property\VarToPublicPropertyRector\VarToPublicPropertyRectorTest
 */
final class VarToPublicPropertyRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused private method', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeController
{
    var $name = 'Tom';
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeController
{
    public $name = 'Tom';
}
CODE_SAMPLE
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

        $this->visibilityManipulator->makePublic($node);

        return $node;
    }
}
