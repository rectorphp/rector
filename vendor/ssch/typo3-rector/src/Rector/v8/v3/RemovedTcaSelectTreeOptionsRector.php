<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v3;

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
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.3/Breaking-77081-RemovedTCASelectTreeOptions.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v3\RemovedTcaSelectTreeOptionsRector\RemovedTcaSelectTreeOptionsRectorTest
 */
final class RemovedTcaSelectTreeOptionsRector extends \Rector\Core\Rector\AbstractRector
{
    use TcaHelperTrait;
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
        $columnItems = $columnsArrayItem->value;
        if (!$columnItems instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $hasAstBeenChanged = \false;
        foreach ($columnItems->items as $columnItem) {
            if (!$columnItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if (null === $columnItem->key) {
                continue;
            }
            if (!$columnItem->value instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            foreach ($columnItem->value->items as $configValue) {
                if (null === $configValue) {
                    continue;
                }
                if (null === $configValue->key) {
                    continue;
                }
                if (!$configValue->value instanceof \PhpParser\Node\Expr\Array_) {
                    continue;
                }
                if (!$this->configIsOfRenderType($configValue->value, 'selectTree')) {
                    continue;
                }
                foreach ($configValue->value->items as $configItemValue) {
                    if (!$configItemValue instanceof \PhpParser\Node\Expr\ArrayItem) {
                        continue;
                    }
                    if (null === $configItemValue->key) {
                        continue;
                    }
                    if ($this->valueResolver->isValue($configItemValue->key, 'autoSizeMax')) {
                        $configItemValue->key = new \PhpParser\Node\Scalar\String_('size');
                    } elseif ($configItemValue->value instanceof \PhpParser\Node\Expr\Array_ && $this->valueResolver->isValue($configItemValue->key, 'treeConfig')) {
                        foreach ($configItemValue->value->items as $treeConfigValue) {
                            if (!$treeConfigValue instanceof \PhpParser\Node\Expr\ArrayItem) {
                                continue;
                            }
                            if (null === $treeConfigValue->key) {
                                continue;
                            }
                            if (!$this->valueResolver->isValue($treeConfigValue->key, 'appearance')) {
                                continue;
                            }
                            if (!$treeConfigValue->value instanceof \PhpParser\Node\Expr\Array_) {
                                continue;
                            }
                            foreach ($treeConfigValue->value->items as $appearanceConfigValue) {
                                if (!$appearanceConfigValue instanceof \PhpParser\Node\Expr\ArrayItem) {
                                    continue;
                                }
                                if (null === $appearanceConfigValue->key) {
                                    continue;
                                }
                                if (!$this->valueResolver->isValues($appearanceConfigValue->key, ['width', 'allowRecursiveMode'])) {
                                    continue;
                                }
                                $this->removeNode($appearanceConfigValue);
                                $hasAstBeenChanged = \true;
                            }
                        }
                    }
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
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Removed TCA tree options: width, allowRecursiveMode, autoSizeMax', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
return [
    'ctrl' => [
    ],
    'columns' => [
        'categories' => [
            'config' => [
                'type' => 'input',
                'renderType' => 'selectTree',
                'autoSizeMax' => 5,
                'treeConfig' => [
                    'appearance' => [
                        'width' => 100,
                        'allowRecursiveMode' => true
                    ]
                ]
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
        'categories' => [
            'config' => [
                'type' => 'input',
                'renderType' => 'selectTree',
                'size' => 5,
                'treeConfig' => [
                    'appearance' => []
                ]
            ],
        ],
    ],
];
CODE_SAMPLE
)]);
    }
}
