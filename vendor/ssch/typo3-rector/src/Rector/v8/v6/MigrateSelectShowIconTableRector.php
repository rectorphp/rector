<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v6;

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
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.6/Deprecation-79440-TcaChanges.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v6\MigrateSelectShowIconTableRector\MigrateSelectShowIconTableRectorTest
 */
final class MigrateSelectShowIconTableRector extends AbstractRector
{
    use TcaHelperTrait;
    /**
     * @var string
     */
    private const DISABLED = 'disabled';
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
        $columnsArrayItem = $this->extractColumns($node);
        if (!$columnsArrayItem instanceof ArrayItem) {
            return null;
        }
        $columnItems = $columnsArrayItem->value;
        if (!$columnItems instanceof Array_) {
            return null;
        }
        $hasAstBeenChanged = \false;
        foreach ($columnItems->items as $fieldValue) {
            if (!$fieldValue instanceof ArrayItem) {
                continue;
            }
            if (null === $fieldValue->key) {
                continue;
            }
            if (!$fieldValue->value instanceof Array_) {
                continue;
            }
            foreach ($fieldValue->value->items as $configValue) {
                if (null === $configValue) {
                    continue;
                }
                if (!$configValue->value instanceof Array_) {
                    continue;
                }
                if (!$this->isConfigType($configValue->value, 'select')) {
                    continue;
                }
                if (!$this->hasRenderType($configValue->value)) {
                    continue;
                }
                foreach ($configValue->value->items as $configItemValue) {
                    if (!$configItemValue instanceof ArrayItem) {
                        continue;
                    }
                    if (null === $configItemValue->key) {
                        continue;
                    }
                    if (!$this->valueResolver->isValues($configItemValue->key, ['selicon_cols', 'showIconTable'])) {
                        continue;
                    }
                    if ($this->shouldAddFieldWizard($configItemValue)) {
                        $fieldWizard = $this->extractArrayItemByKey($configValue->value, 'fieldWizard');
                        if (!$fieldWizard instanceof ArrayItem) {
                            $configValue->value->items[] = new ArrayItem($this->nodeFactory->createArray(['selectIcons' => [self::DISABLED => \false]]), new String_('fieldWizard'));
                        } elseif (($selectIconsArray = $this->extractSubArrayByKey($fieldWizard->value, 'selectIcons')) !== null) {
                            if (null === $this->extractArrayItemByKey($selectIconsArray, self::DISABLED)) {
                                $selectIconsArray->items[] = new ArrayItem($this->nodeFactory->createFalse(), new String_(self::DISABLED));
                            }
                        }
                    }
                    $this->removeNode($configItemValue);
                    $hasAstBeenChanged = \true;
                }
            }
        }
        return $hasAstBeenChanged ? $node : null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrate select showIconTable', [new CodeSample(<<<'CODE_SAMPLE'
return [
    'ctrl' => [
    ],
    'columns' => [
        'foo' => [
            'config' => [
                'type' => 'select',
                'items' => [
                    ['foo 1', 'foo1', 'EXT:styleguide/Resources/Public/Icons/tx_styleguide.svg'],
                    ['foo 2', 'foo2', 'EXT:styleguide/Resources/Public/Icons/tx_styleguide.svg'],
                ],
                'renderType' => 'selectSingle',
                'selicon_cols' => 16,
                'showIconTable' => true
            ],
        ],
    ],
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    'ctrl' => [
    ],
    'columns' => [
        'foo' => [
            'config' => [
                'type' => 'select',
                'items' => [
                    ['foo 1', 'foo1', 'EXT:styleguide/Resources/Public/Icons/tx_styleguide.svg'],
                    ['foo 2', 'foo2', 'EXT:styleguide/Resources/Public/Icons/tx_styleguide.svg'],
                ],
                'renderType' => 'selectSingle',
                'fieldWizard' => [
                    'selectIcons' => [
                        'disabled' => false,
                    ],
                ],
            ],
        ],
    ],
];
CODE_SAMPLE
)]);
    }
    private function shouldAddFieldWizard(ArrayItem $configValueArrayItem) : bool
    {
        if (null === $configValueArrayItem->key) {
            return \false;
        }
        if (!$this->valueResolver->isValue($configValueArrayItem->key, 'showIconTable')) {
            return \false;
        }
        return $this->valueResolver->isTrue($configValueArrayItem->value);
    }
}
