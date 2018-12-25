<?php declare(strict_types=1);

namespace Rector\DeadCode\Rector\Array_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class RemoveDuplicatedArrayKeyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove duplicated key in defined arrays.', [
            new CodeSample(
                <<<'CODE_SAMPLE'
$item = [
    1 => 'A',
    1 => 'A'
];
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$item = [
    1 => 'A',
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
        $arrayKeyValues = [];
        foreach ($node->items as $arrayItem) {
            if ($arrayItem->key === null) {
                continue;
            }

            $keyAndValue = $this->print($arrayItem->key) . $this->print($arrayItem->value);

            // already set the same value
            if (in_array($keyAndValue, $arrayKeyValues, true)) {
                $this->removeNode($arrayItem);
            } else {
                $arrayKeyValues[] = $keyAndValue;
            }
        }

        return $node;
    }
}
