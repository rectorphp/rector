<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v6;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\ArrayUtility;
use Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.6/Deprecation-78899-TCACtrlFieldRequestUpdateDropped.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v6\MoveRequestUpdateOptionFromControlToColumnsRector\MoveRequestUpdateOptionFromControlToColumnsRectorTest
 */
final class MoveRequestUpdateOptionFromControlToColumnsRector extends AbstractRector
{
    use TcaHelperTrait;
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Return_::class];
    }
    /**
     * @param Return_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isFullTca($node)) {
            return null;
        }
        $ctrlArrayItem = $this->extractCtrl($node);
        if (!$ctrlArrayItem instanceof ArrayItem) {
            return null;
        }
        $ctrlItems = $ctrlArrayItem->value;
        if (!$ctrlItems instanceof Array_) {
            return null;
        }
        $requestUpdateFields = [];
        foreach ($ctrlItems->items as $fieldValue) {
            if (!$fieldValue instanceof ArrayItem) {
                continue;
            }
            if (null === $fieldValue->key) {
                continue;
            }
            if ($this->valueResolver->isValue($fieldValue->key, 'requestUpdate')) {
                $fields = $this->valueResolver->getValue($fieldValue->value);
                if (null === $fields) {
                    return null;
                }
                $requestUpdateFields = ArrayUtility::trimExplode(',', $fields);
                $this->removeNode($fieldValue);
            }
        }
        if ([] === $requestUpdateFields) {
            return null;
        }
        $columnsArrayItem = $this->extractColumns($node);
        if (!$columnsArrayItem instanceof ArrayItem) {
            return null;
        }
        $columnItems = $columnsArrayItem->value;
        if (!$columnItems instanceof Array_) {
            return null;
        }
        foreach ($columnItems->items as $columnItem) {
            if (!$columnItem instanceof ArrayItem) {
                continue;
            }
            if (null === $columnItem->key) {
                continue;
            }
            $fieldName = $this->valueResolver->getValue($columnItem->key);
            if (!\in_array($fieldName, $requestUpdateFields, \true)) {
                continue;
            }
            if (!$columnItem->value instanceof Array_) {
                continue;
            }
            $columnItem->value->items[] = new ArrayItem(new String_('reload'), new String_('onChange'));
        }
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('TCA ctrl field requestUpdate dropped', [new CodeSample(<<<'CODE_SAMPLE'
return [
    'ctrl' => [
        'requestUpdate' => 'foo',
    ],
    'columns' => [
        'foo' => []
    ]
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    'ctrl' => [
    ],
    'columns' => [
        'foo' => [
            'onChange' => 'reload'
        ]
    ]
];
CODE_SAMPLE
)]);
    }
}
