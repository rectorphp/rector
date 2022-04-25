<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v7;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.7/Deprecation-79770-DeprecateInlineLocalizationMode.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v7\RemoveLocalizationModeKeepIfNeededRector\RemoveLocalizationModeKeepIfNeededRectorTest
 */
final class RemoveLocalizationModeKeepIfNeededRector extends \Rector\Core\Rector\AbstractRector
{
    use TcaHelperTrait;
    /**
     * @var string
     */
    private const LOCALIZATION_MODE = 'localizationMode';
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove localizationMode keep if allowLanguageSynchronization is enabled', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
return [
    'columns' => [
        'foo' => [
            'label' => 'Bar',
            'config' => [
                'type' => 'inline',
                'appearance' => [
                    'behaviour' => [
                        'localizationMode' => 'keep',
                        'allowLanguageSynchronization' => true,
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
        'foo' => [
            'label' => 'Bar',
            'config' => [
                'type' => 'inline',
                'appearance' => [
                    'behaviour' => [
                        'allowLanguageSynchronization' => true,
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
            $fieldName = $this->valueResolver->getValue($columnItem->key);
            if (null === $fieldName) {
                continue;
            }
            if (!$columnItem->value instanceof \PhpParser\Node\Expr\Array_) {
                continue;
            }
            foreach ($columnItem->value->items as $columnItemConfiguration) {
                if (null === $columnItemConfiguration) {
                    continue;
                }
                if (!$columnItemConfiguration->value instanceof \PhpParser\Node\Expr\Array_) {
                    continue;
                }
                if (!$this->isInlineType($columnItemConfiguration->value)) {
                    continue;
                }
                foreach ($columnItemConfiguration->value->items as $configItemValue) {
                    if (!$configItemValue instanceof \PhpParser\Node\Expr\ArrayItem) {
                        continue;
                    }
                    if (null === $configItemValue->key) {
                        continue;
                    }
                    if (!$this->valueResolver->isValue($configItemValue->key, 'behaviour')) {
                        continue;
                    }
                    if (!$configItemValue->value instanceof \PhpParser\Node\Expr\Array_) {
                        continue;
                    }
                    if (!$this->isLocalizationModeKeepAndAllowLanguageSynchronization($configItemValue->value)) {
                        continue;
                    }
                    foreach ($configItemValue->value->items as $behaviourConfigurationItem) {
                        if (!$behaviourConfigurationItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                            continue;
                        }
                        if (null === $behaviourConfigurationItem->key) {
                            continue;
                        }
                        if ($this->valueResolver->isValue($behaviourConfigurationItem->key, self::LOCALIZATION_MODE)) {
                            $this->removeNode($behaviourConfigurationItem);
                            $hasAstBeenChanged = \true;
                            break;
                        }
                    }
                }
            }
        }
        return $hasAstBeenChanged ? $node : null;
    }
    private function isLocalizationModeKeepAndAllowLanguageSynchronization(\PhpParser\Node\Expr\Array_ $behaviourConfigurationArray) : bool
    {
        $localizationMode = null;
        $allowLanguageSynchronization = null;
        foreach ($behaviourConfigurationArray->items as $behaviourConfigurationItem) {
            if (!$behaviourConfigurationItem instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if (null === $behaviourConfigurationItem->key) {
                continue;
            }
            if (!$this->valueResolver->isValues($behaviourConfigurationItem->key, [self::LOCALIZATION_MODE, 'allowLanguageSynchronization'])) {
                continue;
            }
            $behaviourConfigurationValue = $this->valueResolver->getValue($behaviourConfigurationItem->value);
            if ($this->valueResolver->isValue($behaviourConfigurationItem->key, self::LOCALIZATION_MODE)) {
                $localizationMode = $behaviourConfigurationValue;
            } else {
                $allowLanguageSynchronization = $behaviourConfigurationValue;
            }
        }
        return $allowLanguageSynchronization && 'keep' === $localizationMode;
    }
}
