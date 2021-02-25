<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://3v4l.org/SG0Wu
 * @see \Rector\DeadCode\Tests\Rector\Array_\RemoveDuplicatedArrayKeyRector\RemoveDuplicatedArrayKeyRectorTest
 */
final class RemoveDuplicatedArrayKeyRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove duplicated key in defined arrays.', [
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
        $arrayItemsWithDuplicatedKey = $this->getArrayItemsWithDuplicatedKey($node);
        if ($arrayItemsWithDuplicatedKey === []) {
            return null;
        }

        foreach ($arrayItemsWithDuplicatedKey as $singleArrayItemsWithDuplicatedKey) {
            // keep last item
            array_pop($singleArrayItemsWithDuplicatedKey);
            $this->removeNodes($singleArrayItemsWithDuplicatedKey);
        }

        return $node;
    }

    /**
     * @return ArrayItem[][]
     */
    private function getArrayItemsWithDuplicatedKey(Array_ $array): array
    {
        $arrayItemsByKeys = [];

        foreach ($array->items as $item) {
            if (! $item instanceof ArrayItem) {
                continue;
            }

            if ($item->key === null) {
                continue;
            }

            $keyValue = $this->print($item->key);
            $arrayItemsByKeys[$keyValue][] = $item;
        }

        return $this->filterItemsWithSameKey($arrayItemsByKeys);
    }

    /**
     * @param ArrayItem[][] $arrayItemsByKeys
     * @return ArrayItem[][]
     */
    private function filterItemsWithSameKey(array $arrayItemsByKeys): array
    {
        /** @var ArrayItem[][] $arrayItemsByKeys */
        $arrayItemsByKeys = array_filter($arrayItemsByKeys, function (array $arrayItems): bool {
            return count($arrayItems) > 1;
        });

        return array_filter($arrayItemsByKeys, function (array $arrayItems): bool {
            return count($arrayItems) > 1;
        });
    }
}
