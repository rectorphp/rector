<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v7;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.7/Deprecation-80000-InlineOverrideChildTca.html?highlight=foreign_types
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v7\MoveForeignTypesToOverrideChildTcaRector\MoveForeignTypesToOverrideChildTcaRectorTest
 */
final class MoveForeignTypesToOverrideChildTcaRector extends \Rector\Core\Rector\AbstractRector
{
    use TcaHelperTrait;
    /**
     * @var string
     */
    private const FOREIGN_TYPES = 'foreign_types';
    /**
     * @var string
     */
    private const FOREIGN_SELECTOR_FIELDTCAOVERRIDE = 'foreign_selector_fieldTcaOverride';
    /**
     * @var string
     */
    private const FOREIGN_SELECTOR = 'foreign_selector';
    /**
     * @var string
     */
    private const FOREIGN_RECORD_DEFAULTS = 'foreign_record_defaults';
    /**
     * @var string
     */
    private const OVERRIDE_CHILD_TCA = 'overrideChildTca';
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('TCA InlineOverrideChildTca', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
return [
    'columns' => [
        'aField' => [
            'config' => [
                'type' => 'inline',
                'foreign_types' => [
                    'aForeignType' => [
                        'showitem' => 'aChildField',
                    ],
                ],
            ],
        ],
    ],
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    'columns' => [
        'aField' => [
            'config' => [
                'type' => 'inline',
                'overrideChildTca' => [
                    'types' => [
                        'aForeignType' => [
                            'showitem' => 'aChildField',
                        ],
                    ],
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
     * @return ?Return_
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
        if (!$columns->value instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $hasAstBeenChanged = \false;
        foreach ($this->extractColumnConfig($columns->value) as $columnConfig) {
            //handle the special case of ExtensionManagementUtility::getFileFieldTCAConfig
            $columnConfig = $this->extractConfigFromGetFileFieldTcaConfig($columnConfig);
            if (!$columnConfig instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            $foreignTypesArrayItem = $this->extractArrayItemByKey($columnConfig, self::FOREIGN_TYPES);
            $foreignRecordDefaultsArrayItem = $this->extractArrayItemByKey($columnConfig, self::FOREIGN_RECORD_DEFAULTS);
            $foreignSelectorArrayItem = $this->extractArrayItemByKey($columnConfig, self::FOREIGN_SELECTOR);
            $overrideChildTcaArray = $this->extractSubArrayByKey($columnConfig, self::OVERRIDE_CHILD_TCA);
            $foreignSelectorOverrideArrayItem = $this->extractArrayItemByKey($columnConfig, self::FOREIGN_SELECTOR_FIELDTCAOVERRIDE);
            // don't search further if no foreign_types is configured
            if (!$foreignSelectorOverrideArrayItem instanceof \PhpParser\Node\Expr\ArrayItem && !$foreignTypesArrayItem instanceof \PhpParser\Node\Expr\ArrayItem && !$foreignRecordDefaultsArrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            $foreignSelector = null !== $foreignSelectorArrayItem ? $foreignSelectorArrayItem->value : null;
            if (!$overrideChildTcaArray instanceof \PhpParser\Node\Expr\Array_) {
                $overrideChildTcaArray = new \PhpParser\Node\Expr\Array_();
                $columnConfig->items[] = new \PhpParser\Node\Expr\ArrayItem($overrideChildTcaArray, new \PhpParser\Node\Scalar\String_(self::OVERRIDE_CHILD_TCA));
            }
            if (null !== $foreignTypesArrayItem && $foreignTypesArrayItem->value instanceof \PhpParser\Node\Expr\Array_) {
                $this->injectOverrideChildTca($overrideChildTcaArray, 'types', $foreignTypesArrayItem->value);
                $this->removeNode($foreignTypesArrayItem);
                $hasAstBeenChanged = \true;
            }
            if (null !== $foreignSelectorOverrideArrayItem && $foreignSelectorOverrideArrayItem->value instanceof \PhpParser\Node\Expr\Array_ && $foreignSelector instanceof \PhpParser\Node\Scalar\String_) {
                $columnItem = new \PhpParser\Node\Expr\Array_([new \PhpParser\Node\Expr\ArrayItem($foreignSelectorOverrideArrayItem->value, new \PhpParser\Node\Scalar\String_($foreignSelector->value))]);
                $this->injectOverrideChildTca($overrideChildTcaArray, 'columns', $columnItem);
                $this->removeNode($foreignSelectorOverrideArrayItem);
                $hasAstBeenChanged = \true;
            }
            if (null !== $foreignRecordDefaultsArrayItem && $foreignRecordDefaultsArrayItem->value instanceof \PhpParser\Node\Expr\Array_) {
                $newOverrideColumns = new \PhpParser\Node\Expr\Array_();
                foreach ($foreignRecordDefaultsArrayItem->value->items as $item) {
                    if (!$item instanceof \PhpParser\Node\Expr\ArrayItem) {
                        continue;
                    }
                    $value = new \PhpParser\Node\Expr\Array_([new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Expr\Array_([new \PhpParser\Node\Expr\ArrayItem($item->value, new \PhpParser\Node\Scalar\String_('default'))]), new \PhpParser\Node\Scalar\String_('config'))]);
                    $newOverrideColumns->items[] = new \PhpParser\Node\Expr\ArrayItem($value, $item->key);
                }
                $this->injectOverrideChildTca($overrideChildTcaArray, 'columns', $newOverrideColumns);
                $this->removeNode($foreignRecordDefaultsArrayItem);
                $hasAstBeenChanged = \true;
            }
        }
        return $hasAstBeenChanged ? $node : null;
    }
    private function extractConfigFromGetFileFieldTcaConfig(\PhpParser\Node $columnConfig) : \PhpParser\Node
    {
        if ($columnConfig instanceof \PhpParser\Node\Expr\StaticCall) {
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($columnConfig, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility'))) {
                return $columnConfig;
            }
            if (!$this->isName($columnConfig->name, 'getFileFieldTCAConfig')) {
                return $columnConfig;
            }
            if (\count($columnConfig->args) < 2) {
                return $columnConfig;
            }
            if (!$columnConfig->args[1]->value instanceof \PhpParser\Node\Expr\Array_) {
                return $columnConfig;
            }
            return $columnConfig->args[1]->value;
        }
        return $columnConfig;
    }
    private function injectOverrideChildTca(\PhpParser\Node\Expr\Array_ $overrideChildTcaNode, string $overrideKey, \PhpParser\Node\Expr\Array_ $overrideValue) : void
    {
        $newOverrideChildTcaSetting = $this->extractArrayItemByKey($overrideChildTcaNode, $overrideKey);
        if (!$newOverrideChildTcaSetting instanceof \PhpParser\Node\Expr\ArrayItem) {
            $newOverrideChildTcaSetting = new \PhpParser\Node\Expr\ArrayItem($overrideValue, new \PhpParser\Node\Scalar\String_($overrideKey));
            $overrideChildTcaNode->items[] = $newOverrideChildTcaSetting;
        } else {
            if (!$newOverrideChildTcaSetting->value instanceof \PhpParser\Node\Expr\Array_) {
                // do not alter overrideChildTca nodes that are not an array (which would be invalid tca, but lets be sure here)
                return;
            }
            $newOverrideChildTcaSetting->value->items = \array_merge($newOverrideChildTcaSetting->value->items, $overrideValue->items);
        }
    }
}
