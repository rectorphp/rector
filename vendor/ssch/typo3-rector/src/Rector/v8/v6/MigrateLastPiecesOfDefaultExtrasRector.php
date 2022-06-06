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
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.6/Deprecation-79440-TcaChanges.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v6\MigrateLastPiecesOfDefaultExtrasRector\MigrateLastPiecesOfDefaultExtrasRectorTest
 */
final class MigrateLastPiecesOfDefaultExtrasRector extends \Rector\Core\Rector\AbstractRector
{
    use TcaHelperTrait;
    /**
     * @var bool
     */
    private $hasAstBeenChanged = \false;
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
        $this->refactorDefaultExtras($columnItems);
        $types = $this->extractTypes($node);
        if (!$types instanceof \PhpParser\Node\Expr\ArrayItem) {
            return $this->hasAstBeenChanged ? $node : null;
        }
        $typesItems = $types->value;
        if (!$typesItems instanceof \PhpParser\Node\Expr\Array_) {
            return $this->hasAstBeenChanged ? $node : null;
        }
        foreach ($typesItems->items as $typesItem) {
            if (!$typesItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if (null === $typesItem->key) {
                continue;
            }
            if (!$typesItem->value instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            foreach ($typesItem->value->items as $configValue) {
                if (null === $configValue) {
                    continue;
                }
                if (null === $configValue->key) {
                    continue;
                }
                if (!$this->valueResolver->isValue($configValue->key, 'columnsOverrides')) {
                    continue;
                }
                if (!$configValue->value instanceof \PhpParser\Node\Expr\Array_) {
                    continue;
                }
                $this->refactorDefaultExtras($configValue->value);
            }
        }
        return $this->hasAstBeenChanged ? $node : null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrate last pieces of default extras', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
return [
            'ctrl' => [],
            'columns' => [
                'constants' => [
                    'label' => 'Foo',
                    'config' => [
                        'type' => 'text',
                        'cols' => 48,
                        'rows' => 15,
                    ],
                    'defaultExtras' => 'rte_only:nowrap:enable-tab:fixed-font'
                ],
            ],
            'types' => [
                'myType' => [
                    'columnsOverrides' => [
                        'constants' => [
                            'label' => 'Foo',
                            'config' => [
                                'type' => 'text',
                                'cols' => 48,
                                'rows' => 15,
                            ],
                            'defaultExtras' => 'rte_only:nowrap:enable-tab:fixed-font'
                        ],
                    ],
                ],
            ],
        ];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
            'ctrl' => [],
            'columns' => [
                'constants' => [
                    'label' => 'Foo',
                    'config' => [
                        'type' => 'text',
                        'cols' => 48,
                        'rows' => 15,
                        'wrap' => 'off',
                        'enableTabulator' => true,
                        'fixedFont' => true,
                    ]
                ],
            ],
            'types' => [
                'myType' => [
                    'columnsOverrides' => [
                        'constants' => [
                            'label' => 'Foo',
                            'config' => [
                                'type' => 'text',
                                'cols' => 48,
                                'rows' => 15,
                                'wrap' => 'off',
                                'enableTabulator' => true,
                                'fixedFont' => true,
                            ]
                        ],
                    ],
                ],
            ],
        ];
CODE_SAMPLE
)]);
    }
    private function refactorDefaultExtras(\PhpParser\Node\Expr\Array_ $columnItemsArray) : void
    {
        foreach ($columnItemsArray->items as $columnItem) {
            if (!$columnItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if (null === $columnItem->key) {
                continue;
            }
            if (!$columnItem->value instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            $additionalConfigItems = [];
            foreach ($columnItem->value->items as $configValue) {
                if (null === $configValue) {
                    continue;
                }
                if (null === $configValue->key) {
                    continue;
                }
                if (!$this->valueResolver->isValue($configValue->key, 'defaultExtras')) {
                    continue;
                }
                $defaultExtras = $this->valueResolver->getValue($configValue->value);
                if (!\is_string($defaultExtras)) {
                    continue;
                }
                $defaultExtrasArray = \Ssch\TYPO3Rector\Helper\ArrayUtility::trimExplode(':', $defaultExtras, \true);
                foreach ($defaultExtrasArray as $defaultExtrasSetting) {
                    if ('nowrap' === $defaultExtrasSetting) {
                        $additionalConfigItems[] = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Scalar\String_('off'), new \PhpParser\Node\Scalar\String_('wrap'));
                    } elseif ('enable-tab' === $defaultExtrasSetting) {
                        $additionalConfigItems[] = new \PhpParser\Node\Expr\ArrayItem($this->nodeFactory->createTrue(), new \PhpParser\Node\Scalar\String_('enableTabulator'));
                    } elseif ('fixed-font' === $defaultExtrasSetting) {
                        $additionalConfigItems[] = new \PhpParser\Node\Expr\ArrayItem($this->nodeFactory->createTrue(), new \PhpParser\Node\Scalar\String_('fixedFont'));
                    }
                }
                // Remove the defaultExtras
                $this->removeNode($configValue);
            }
            if ([] !== $additionalConfigItems) {
                $this->hasAstBeenChanged = \true;
                $config = $this->extractArrayItemByKey($columnItem->value, 'config');
                if (!$config instanceof \PhpParser\Node\Expr\ArrayItem) {
                    $config = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Expr\Array_(), new \PhpParser\Node\Scalar\String_('config'));
                    $columnItem->value->items[] = $config;
                }
                if (!$config->value instanceof \PhpParser\Node\Expr\Array_) {
                    continue;
                }
                foreach ($additionalConfigItems as $additionalConfigItem) {
                    $config->value->items[] = $additionalConfigItem;
                }
            }
        }
    }
}
