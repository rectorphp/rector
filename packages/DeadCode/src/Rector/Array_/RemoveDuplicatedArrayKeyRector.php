<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/SG0Wu
 */
final class RemoveDuplicatedArrayKeyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove duplicated key in defined arrays.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$item = [
    1 => 'A',
    1 => 'B'
];
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$item = [
    1 => 'B'
];
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Array_::class];
    }

    /**
     * @param Array_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $arrayItemsByKeys = [];

        foreach ($node->items as $arrayItem) {
            if ($arrayItem->key === null) {
                continue;
            }

            $keyValue = $this->print($arrayItem->key);
            $arrayItemsByKeys[$keyValue][] = $arrayItem;
        }

        foreach ($arrayItemsByKeys as $arrayItems) {
            // keep last item
            array_pop($arrayItems);

            $this->removeNodes($arrayItems);
        }

        return $node;
    }
}
