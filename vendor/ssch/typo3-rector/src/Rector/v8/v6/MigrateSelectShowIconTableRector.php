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
final class MigrateSelectShowIconTableRector extends \Rector\Core\Rector\AbstractRector
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
        $columns = $this->extractColumns($node);
        if (!$columns instanceof \PhpParser\Node\Expr\ArrayItem) {
            return null;
        }
        $columnItems = $columns->value;
        if (!$columnItems instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $hasAstBeenChanged = \false;
        foreach ($columnItems->items as $fieldValue) {
            if (!$fieldValue instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if (null === $fieldValue->key) {
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
                if (!$this->isConfigType($configValue->value, 'select')) {
                    continue;
                }
                if (!$this->hasRenderType($configValue->value)) {
                    continue;
                }
                foreach ($configValue->value->items as $configItemValue) {
                    if (!$configItemValue instanceof \PhpParser\Node\Expr\ArrayItem) {
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
                        if (!$fieldWizard instanceof \PhpParser\Node\Expr\ArrayItem) {
                            $configValue->value->items[] = new \PhpParser\Node\Expr\ArrayItem($this->nodeFactory->createArray(['selectIcons' => [self::DISABLED => \false]]), new \PhpParser\Node\Scalar\String_('fieldWizard'));
                        } elseif (($selectIcons = $this->extractSubArrayByKey($fieldWizard->value, 'selectIcons')) !== null) {
                            if (null === $this->extractArrayItemByKey($selectIcons, self::DISABLED)) {
                                $selectIcons->items[] = new \PhpParser\Node\Expr\ArrayItem($this->nodeFactory->createFalse(), new \PhpParser\Node\Scalar\String_(self::DISABLED));
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
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrate select showIconTable', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
    private function shouldAddFieldWizard(\PhpParser\Node\Expr\ArrayItem $configItemValue) : bool
    {
        if (null === $configItemValue->key) {
            return \false;
        }
        if (!$this->valueResolver->isValue($configItemValue->key, 'showIconTable')) {
            return \false;
        }
        return $this->valueResolver->isTrue($configItemValue->value);
    }
}
