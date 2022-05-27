<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v3;

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
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.3/Breaking-77156-TSconfigAndTStemplateSoftReferencesFunctionalityRemoved.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v3\SoftReferencesFunctionalityRemovedRector\SoftReferencesFunctionalityRemovedRectorTest
 */
final class SoftReferencesFunctionalityRemovedRector extends AbstractRector
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
        $columnsArrayItem = $this->extractColumns($node);
        if (!$columnsArrayItem instanceof ArrayItem) {
            return null;
        }
        $columnItems = $columnsArrayItem->value;
        if (!$columnItems instanceof Array_) {
            return null;
        }
        $hasAstBeenChanged = \false;
        foreach ($columnItems->items as $columnItem) {
            if (!$columnItem instanceof ArrayItem) {
                continue;
            }
            if (null === $columnItem->key) {
                continue;
            }
            if (!$columnItem->value instanceof Array_) {
                continue;
            }
            foreach ($columnItem->value->items as $configValue) {
                if (null === $configValue) {
                    continue;
                }
                if (null === $configValue->key) {
                    continue;
                }
                if (!$configValue->value instanceof Array_) {
                    continue;
                }
                $configFieldName = $this->valueResolver->getValue($configValue->key);
                if ('config' !== $configFieldName) {
                    continue;
                }
                foreach ($configValue->value->items as $configItemValue) {
                    if (null === $configItemValue) {
                        continue;
                    }
                    if (null === $configItemValue->key) {
                        continue;
                    }
                    if (!$this->valueResolver->isValue($configItemValue->key, 'softref')) {
                        continue;
                    }
                    $configItemValueValue = $this->valueResolver->getValue($configItemValue->value);
                    if (null === $configItemValueValue) {
                        continue;
                    }
                    $softReferences = \array_flip(ArrayUtility::trimExplode(',', $configItemValueValue));
                    $changed = \false;
                    if (isset($softReferences['TSconfig'])) {
                        $changed = \true;
                        unset($softReferences['TSconfig']);
                    }
                    if (isset($softReferences['TStemplate'])) {
                        $changed = \true;
                        unset($softReferences['TStemplate']);
                    }
                    if ($changed) {
                        if ([] !== $softReferences) {
                            $softReferences = \array_flip($softReferences);
                            $configItemValue->value = new String_(\implode(',', $softReferences));
                        } else {
                            $this->removeNode($configItemValue);
                        }
                        $hasAstBeenChanged = \true;
                    }
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
        return new RuleDefinition('TSconfig and TStemplate soft references functionality removed', [new CodeSample(<<<'CODE_SAMPLE'
return [
    'ctrl' => [
    ],
    'columns' => [
        'TSconfig' => [
            'label' => 'TSconfig:',
            'config' => [
                'type' => 'text',
                'cols' => '40',
                'rows' => '5',
                'softref' => 'TSconfig',
            ],
            'defaultExtras' => 'fixed-font : enable-tab',
        ],
    ],
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    'ctrl' => [
    ],
    'columns' => [
        'TSconfig' => [
            'label' => 'TSconfig:',
            'config' => [
                'type' => 'text',
                'cols' => '40',
                'rows' => '5',
            ],
            'defaultExtras' => 'fixed-font : enable-tab',
        ],
    ],
];
CODE_SAMPLE
)]);
    }
}
