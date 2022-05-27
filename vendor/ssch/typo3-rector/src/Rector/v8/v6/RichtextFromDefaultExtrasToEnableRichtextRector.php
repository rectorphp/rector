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
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.6/Deprecation-79341-TCARichtextConfigurationInDefaultExtrasDropped.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v6\RichtextFromDefaultExtrasToEnableRichtextRector\RichtextFromDefaultExtrasToEnableRichtextRectorTest
 */
final class RichtextFromDefaultExtrasToEnableRichtextRector extends AbstractRector
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
        return [Return_::class];
    }
    /**
     * @param Return_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->hasAstBeenChanged = \false;
        if (!$this->isFullTca($node)) {
            return null;
        }
        $columns = $this->extractColumns($node);
        if (!$columns instanceof ArrayItem) {
            return null;
        }
        $columnItems = $columns->value;
        if (!$columnItems instanceof Array_) {
            return null;
        }
        $this->refactorRichtextColumns($columnItems);
        $types = $this->extractTypes($node);
        if (!$types instanceof ArrayItem) {
            return $this->hasAstBeenChanged ? $node : null;
        }
        $typesItems = $types->value;
        if (!$typesItems instanceof Array_) {
            return $this->hasAstBeenChanged ? $node : null;
        }
        foreach ($typesItems->items as $typesItem) {
            if (!$typesItem instanceof ArrayItem) {
                continue;
            }
            if (null === $typesItem->key) {
                continue;
            }
            if (!$typesItem->value instanceof Array_) {
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
                if (!$configValue->value instanceof Array_) {
                    continue;
                }
                $this->refactorRichtextColumns($configValue->value);
            }
        }
        return $this->hasAstBeenChanged ? $node : null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('TCA richtext configuration in defaultExtras dropped', [new CodeSample(<<<'CODE_SAMPLE'
[
    'columns' => [
        'content' => [
            'config' => [
                'type' => 'text',
            ],
            'defaultExtras' => 'richtext:rte_transform',
        ],
    ],
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
[
    'columns' => [
        'content' => [
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
            ],
        ],
    ],
];
CODE_SAMPLE
)]);
    }
    private function isRichtextInDefaultExtras(ArrayItem $configValueArrayItem) : bool
    {
        if (null === $configValueArrayItem->key) {
            return \false;
        }
        if (!$this->valueResolver->isValue($configValueArrayItem->key, 'defaultExtras')) {
            return \false;
        }
        $defaultExtras = $this->valueResolver->getValue($configValueArrayItem->value);
        if (!\is_string($defaultExtras)) {
            return \false;
        }
        return \strncmp($defaultExtras, 'richtext', \strlen('richtext')) === 0;
    }
    private function refactorRichtextColumns(Array_ $columnItemsArray) : void
    {
        foreach ($columnItemsArray->items as $columnItem) {
            if (!$columnItem instanceof ArrayItem) {
                continue;
            }
            if (null === $columnItem->key) {
                continue;
            }
            if (!$columnItem->value instanceof Array_) {
                continue;
            }
            $hasRichTextConfiguration = \false;
            foreach ($columnItem->value->items as $configValue) {
                if (null === $configValue) {
                    continue;
                }
                if (!$this->isRichtextInDefaultExtras($configValue)) {
                    continue;
                }
                $hasRichTextConfiguration = \true;
                $this->removeNode($configValue);
                $this->hasAstBeenChanged = \true;
            }
            if ($hasRichTextConfiguration) {
                $configurationArray = null;
                foreach ($columnItem->value->items as $configValue) {
                    if (null === $configValue) {
                        continue;
                    }
                    if (null === $configValue->key) {
                        continue;
                    }
                    if (!$this->valueResolver->isValue($configValue->key, 'config')) {
                        continue;
                    }
                    if (!$configValue->value instanceof Array_) {
                        continue;
                    }
                    $configurationArray = $configValue;
                }
                if (null === $configurationArray) {
                    $configurationArray = new ArrayItem(new Array_(), new String_('config'));
                    $columnItem->value->items[] = $configurationArray;
                    $this->hasAstBeenChanged = \true;
                }
                if ($configurationArray instanceof ArrayItem && $configurationArray->value instanceof Array_) {
                    if (null === $this->extractArrayItemByKey($configurationArray->value, 'enableRichtext')) {
                        $configurationArray->value->items[] = new ArrayItem($this->nodeFactory->createTrue(), new String_('enableRichtext'));
                    }
                    if (null === $this->extractArrayItemByKey($configurationArray->value, 'richtextConfiguration')) {
                        $configurationArray->value->items[] = new ArrayItem(new String_('default'), new String_('richtextConfiguration'));
                    }
                    $this->hasAstBeenChanged = \true;
                }
            }
        }
    }
}
