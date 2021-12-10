<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v6;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.6/Deprecation-79440-TcaChanges.html#suggest-wizard
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v6\MoveTypeGroupSuggestWizardToSuggestOptions\MoveTypeGroupSuggestWizardToSuggestOptionsTest
 */
final class MoveTypeGroupSuggestWizardToSuggestOptionsRector extends \Rector\Core\Rector\AbstractRector
{
    use TcaHelperTrait;
    /**
     * @var string
     */
    private const TYPE = 'type';
    /**
     * @var bool
     */
    private $hasAstBeenChanged = \false;
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrate the "suggest" wizard in type=group to "hideSuggest" and "suggestOptions"', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
[
    'columns' => [
        'group_db_8' => [
            'label' => 'group_db_8',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_styleguide_staticdata',
                'wizards' => [
                    '_POSITION' => 'top',
                    'suggest' => [
                        'type' => 'suggest',
                        'default' => [
                            'pidList' => 42,
                        ],
                    ],
                ],
            ],
        ],
    ],
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
[
    'columns' => [
        'group_db_8' => [
            'label' => 'group_db_8',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_styleguide_staticdata',
                'suggestOptions' => [
                    'default' => [
                        'pidList' => 42,
                    ]
                ],
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
        $columns = $this->extractSubArrayByKey($node->expr, 'columns');
        if (!$columns instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $columnNamesWithTypeGroupAndInternalTypeDb = [];
        $this->hasAstBeenChanged = \false;
        foreach ($this->extractColumnConfig($columns) as $columnName => $config) {
            if (!$config instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            if (!$this->hasKeyValuePair($config, self::TYPE, 'group')) {
                continue;
            }
            if (!$this->hasKeyValuePair($config, 'internal_type', 'db')) {
                continue;
            }
            $columnNamesWithTypeGroupAndInternalTypeDb[$columnName] = $config;
            $this->refactorWizards($config);
        }
        // now check columnsOverrides of all type=group, internal_type=db fields:
        $types = $this->extractSubArrayByKey($node->expr, 'types');
        if (!$types instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        foreach ($this->extractColumnConfig($types, 'columnsOverrides') as $columnOverride) {
            if (!$columnOverride instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            foreach ($columnNamesWithTypeGroupAndInternalTypeDb as $columnName => $columnConfig) {
                $overrideForColumn = $this->extractSubArrayByKey($columnOverride, $columnName);
                if (!$overrideForColumn instanceof \PhpParser\Node\Expr\Array_) {
                    continue;
                }
                $configOverride = $this->extractSubArrayByKey($overrideForColumn, 'config');
                if (!$configOverride instanceof \PhpParser\Node\Expr\Array_) {
                    continue;
                }
                if ($this->refactorWizards($configOverride)) {
                    $configOverride->items[] = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('false')), new \PhpParser\Node\Scalar\String_('hideSuggest'));
                    $columnConfig->items[] = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name('true')), new \PhpParser\Node\Scalar\String_('hideSuggest'));
                    $this->hasAstBeenChanged = \true;
                }
            }
        }
        return $this->hasAstBeenChanged ? $node : null;
    }
    private function refactorWizards(\PhpParser\Node\Expr\Array_ $config) : bool
    {
        $wizardsArrayItem = $this->extractArrayItemByKey($config, 'wizards');
        if (!$wizardsArrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
            return \false;
        }
        $wizards = $wizardsArrayItem->value;
        if (!$wizards instanceof \PhpParser\Node\Expr\Array_) {
            return \false;
        }
        foreach ($this->extractSubArraysWithArrayItemMatching($wizards, self::TYPE, 'suggest') as $wizard) {
            $wizardConfig = $wizard->value;
            if (!$wizardConfig instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            $typeItem = $this->extractArrayItemByKey($wizardConfig, self::TYPE);
            if (null !== $typeItem) {
                $this->removeNode($typeItem);
            }
            if (!$this->isEmpty($wizardConfig)) {
                $config->items[] = new \PhpParser\Node\Expr\ArrayItem($wizardConfig, new \PhpParser\Node\Scalar\String_('suggestOptions'));
            }
            $this->removeNode($wizard);
            $this->hasAstBeenChanged = \true;
        }
        if ($this->isEmpty($wizards)) {
            $this->removeNode($wizardsArrayItem);
        }
        return \true;
    }
    private function isEmpty(\PhpParser\Node\Expr\Array_ $array) : bool
    {
        $nodeEmpty = \true;
        foreach ($array->items as $item) {
            if (null !== $item && !$this->nodesToRemoveCollector->isNodeRemoved($item)) {
                if (null === $item->key) {
                    continue;
                }
                if (\strncmp($this->valueResolver->getValue($item->key), '_', \strlen('_')) !== 0) {
                    $nodeEmpty = \false;
                    break;
                }
            }
        }
        return $nodeEmpty;
    }
}
