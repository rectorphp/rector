<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/SG0Wu
 * @see \Rector\DeadCode\Tests\Rector\Array_\RemoveDuplicatedArrayKeyRector\RemoveDuplicatedArrayKeyRectorTest
 */
final class RemoveDuplicatedArrayKeyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove duplicated key in defined arrays.', [
            new CodeSample(
                <<<'PHP'
$item = [
    1 => 'A',
    1 => 'B'
];
PHP
                ,
                <<<'PHP'
$item = [
    1 => 'B'
];
PHP
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
        $arrayItemsWithDuplicatedKey = $this->getArrayItemsWithDuplicatedKey($node);
        if ($arrayItemsWithDuplicatedKey === []) {
            return null;
        }

        foreach ($arrayItemsWithDuplicatedKey as $arrayItems) {
            // keep last item
            array_pop($arrayItems);
            $this->removeNodes($arrayItems);
        }

        return $node;
    }

    /**
     * @return ArrayItem[][]
     */
    private function getArrayItemsWithDuplicatedKey(Array_ $array): array
    {
        $arrayItemsByKeys = [];

        foreach ($array->items as $arrayItem) {
            if (! $arrayItem instanceof ArrayItem) {
                continue;
            }

            if ($arrayItem->key === null) {
                continue;
            }

            $keyValue = $this->print($arrayItem->key);
            $arrayItemsByKeys[$keyValue][] = $arrayItem;
        }

        return $this->filterItemsWithSameKey($arrayItemsByKeys);
    }

    /**
     * @param ArrayItem[][] $arrayItemsByKeys
     * @return \PhpParser\Node\Expr\ArrayItem[]&mixed[][]
     */
    private function filterItemsWithSameKey(array $arrayItemsByKeys): array
    {
        return array_filter($arrayItemsByKeys, function (array $arrayItems): bool {
            return count($arrayItems) > 1;
        });
    }
}
