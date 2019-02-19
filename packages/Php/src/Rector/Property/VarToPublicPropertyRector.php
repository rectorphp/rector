<?php declare(strict_types=1);

namespace Rector\Php\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class VarToPublicPropertyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove unused private method', [
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
        if ($node->flags !== 0) {
            return null;
        }

        $node->flags = Class_::MODIFIER_PUBLIC;

        return $node;
    }
}
