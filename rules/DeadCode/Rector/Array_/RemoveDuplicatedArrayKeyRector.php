<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/SG0Wu
 * @see \Rector\Tests\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector\RemoveDuplicatedArrayKeyRectorTest
 */
final class RemoveDuplicatedArrayKeyRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    public function __construct(\Rector\Core\Contract\PhpParser\NodePrinterInterface $nodePrinter)
    {
        $this->nodePrinter = $nodePrinter;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove duplicated key in defined arrays.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$item = [
    1 => 'A',
    1 => 'B'
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$item = [
    1 => 'B'
];
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Array_::class];
    }
    /**
     * @param Array_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $arrayItemsWithDuplicatedKey = $this->getArrayItemsWithDuplicatedKey($node);
        if ($arrayItemsWithDuplicatedKey === []) {
            return null;
        }
        foreach ($arrayItemsWithDuplicatedKey as $arrayItemWithDuplicatedKey) {
            // keep last item
            \array_pop($arrayItemWithDuplicatedKey);
            $this->nodeRemover->removeNodes($arrayItemWithDuplicatedKey);
        }
        return $node;
    }
    /**
     * @return ArrayItem[][]
     */
    private function getArrayItemsWithDuplicatedKey(\PhpParser\Node\Expr\Array_ $array) : array
    {
        $arrayItemsByKeys = [];
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if ($arrayItem->key === null) {
                continue;
            }
            $keyValue = $this->nodePrinter->print($arrayItem->key);
            $arrayItemsByKeys[$keyValue][] = $arrayItem;
        }
        return $this->filterItemsWithSameKey($arrayItemsByKeys);
    }
    /**
     * @param ArrayItem[][] $arrayItemsByKeys
     * @return ArrayItem[][]
     */
    private function filterItemsWithSameKey(array $arrayItemsByKeys) : array
    {
        /** @var ArrayItem[][] $arrayItemsByKeys */
        $arrayItemsByKeys = \array_filter($arrayItemsByKeys, function (array $arrayItems) : bool {
            return \count($arrayItems) > 1;
        });
        return \array_filter($arrayItemsByKeys, function (array $arrayItems) : bool {
            return \count($arrayItems) > 1;
        });
    }
}
