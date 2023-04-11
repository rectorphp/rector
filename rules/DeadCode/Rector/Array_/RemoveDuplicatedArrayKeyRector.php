<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\MultiInstanceofChecker;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/SG0Wu
 * @see \Rector\Tests\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector\RemoveDuplicatedArrayKeyRectorTest
 */
final class RemoveDuplicatedArrayKeyRector extends AbstractRector
{
    /**
     * @var array<class-string<Expr>>
     */
    private const ALLOWED_KEY_DUPLICATES = [PreInc::class, PreDec::class];
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    /**
     * @readonly
     * @var \Rector\Core\Util\MultiInstanceofChecker
     */
    private $multiInstanceofChecker;
    public function __construct(NodePrinterInterface $nodePrinter, MultiInstanceofChecker $multiInstanceofChecker)
    {
        $this->nodePrinter = $nodePrinter;
        $this->multiInstanceofChecker = $multiInstanceofChecker;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove duplicated key in defined arrays.', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Array_::class];
    }
    /**
     * @param Array_ $node
     */
    public function refactor(Node $node) : ?Node
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
    private function getArrayItemsWithDuplicatedKey(Array_ $array) : array
    {
        $arrayItemsByKeys = [];
        foreach ($array->items as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$arrayItem->key instanceof Expr) {
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
        $result = [];
        foreach ($arrayItemsByKeys as $arrayItems) {
            if (\count($arrayItems) <= 1) {
                continue;
            }
            $currentArrayItem = \current($arrayItems);
            /** @var Expr $currentArrayItemKey */
            $currentArrayItemKey = $currentArrayItem->key;
            if ($this->multiInstanceofChecker->isInstanceOf($currentArrayItemKey, self::ALLOWED_KEY_DUPLICATES)) {
                continue;
            }
            $result[] = $arrayItems;
        }
        return $result;
    }
}
