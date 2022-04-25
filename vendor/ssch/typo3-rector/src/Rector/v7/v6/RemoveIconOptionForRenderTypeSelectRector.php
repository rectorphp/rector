<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v7\v6;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/7.6/Breaking-70033-TcaIconOptionsForSelectFields.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v7\v6\RemoveIconOptionForRenderTypeSelectRector\RemoveIconOptionForRenderTypeSelectRectorTest
 */
final class RemoveIconOptionForRenderTypeSelectRector extends \Rector\Core\Rector\AbstractRector
{
    use TcaHelperTrait;
    /**
     * @var string
     */
    private const SHOW_ICON_TABLE = 'showIconTable';
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('TCA icon options have been removed', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
return [
    'columns' => [
        'foo' => [
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'noIconsBelowSelect' => false,
            ],
        ],
    ],
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    'columns' => [
        'foo' => [
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'showIconTable' => true,
            ],
        ],
    ],
];
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Return_::class];
    }
    /**
     * @param Return_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isFullTca($node)) {
            return null;
        }
        $columnsArrayItem = $this->extractColumns($node);
        if (!$columnsArrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
            return null;
        }
        $items = $columnsArrayItem->value;
        if (!$items instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $hasAstBeenChanged = \false;
        foreach ($items->items as $fieldValue) {
            if (!$fieldValue instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if (null === $fieldValue->key) {
                continue;
            }
            $fieldName = $this->valueResolver->getValue($fieldValue->key);
            if (null === $fieldName) {
                continue;
            }
            if (!$fieldValue->value instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            foreach ($fieldValue->value->items as $configValue) {
                if (null === $configValue) {
                    continue;
                }
                if (!$configValue->value instanceof \PhpParser\Node\Expr\Array_) {
                    continue;
                }
                $renderType = null;
                $selicon_cols = null;
                $showIconTable = null;
                $noIconsBelowSelect = null;
                $doSomething = \false;
                foreach ($configValue->value->items as $configItemValue) {
                    if (!$configItemValue instanceof \PhpParser\Node\Expr\ArrayItem) {
                        continue;
                    }
                    if (null === $configItemValue->key) {
                        continue;
                    }
                    if ($this->valueResolver->isValue($configItemValue->key, 'renderType')) {
                        $renderType = $this->valueResolver->getValue($configItemValue->value);
                    } elseif ($this->valueResolver->isValue($configItemValue->key, 'selicon_cols')) {
                        $selicon_cols = $this->valueResolver->getValue($configItemValue->value);
                        $doSomething = \true;
                    } elseif ($this->valueResolver->isValue($configItemValue->key, self::SHOW_ICON_TABLE)) {
                        $showIconTable = $this->valueResolver->getValue($configItemValue->value);
                    } elseif ($this->valueResolver->isValue($configItemValue->key, 'suppress_icons')) {
                        $this->removeNode($configItemValue);
                        $hasAstBeenChanged = \true;
                    } elseif ($this->valueResolver->isValue($configItemValue->key, 'noIconsBelowSelect')) {
                        $noIconsBelowSelect = $this->valueResolver->getValue($configItemValue->value);
                        $doSomething = \true;
                        $this->removeNode($configItemValue);
                        $hasAstBeenChanged = \true;
                    } elseif ($this->valueResolver->isValue($configItemValue->key, 'foreign_table_loadIcons')) {
                        $this->removeNode($configItemValue);
                        $hasAstBeenChanged = \true;
                    }
                }
                if (!$doSomething) {
                    continue;
                }
                if (null === $renderType || 'selectSingle' !== $renderType) {
                    continue;
                }
                if (null !== $selicon_cols && null === $showIconTable) {
                    $configValue->value->items[] = new \PhpParser\Node\Expr\ArrayItem($this->nodeFactory->createTrue(), new \PhpParser\Node\Scalar\String_(self::SHOW_ICON_TABLE));
                    $hasAstBeenChanged = \true;
                } elseif (!$noIconsBelowSelect && null === $showIconTable) {
                    $configValue->value->items[] = new \PhpParser\Node\Expr\ArrayItem($this->nodeFactory->createTrue(), new \PhpParser\Node\Scalar\String_(self::SHOW_ICON_TABLE));
                    $hasAstBeenChanged = \true;
                }
            }
        }
        return $hasAstBeenChanged ? $node : null;
    }
}
