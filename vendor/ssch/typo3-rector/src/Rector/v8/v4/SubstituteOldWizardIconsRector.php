<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v4;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220606\Webmozart\Assert\Assert;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.4/Breaking-77630-RemoveWizardIcons.html
 * Backport to v7.6 available
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v4\SubstituteOldWizardIconsRector\SubstituteOldWizardIconsRectorTest
 */
final class SubstituteOldWizardIconsRector extends AbstractRector implements ConfigurableRectorInterface
{
    use TcaHelperTrait;
    /**
     * @var string
     */
    public const OLD_TO_NEW_FILE_LOCATIONS = 'old_to_new_file_locations';
    /**
     * @var array<string, string>
     */
    private $oldToNewFileLocations = [];
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
        $items = $columnsArrayItem->value;
        if (!$items instanceof Array_) {
            return null;
        }
        $oldFileNames = \array_keys($this->oldToNewFileLocations);
        $hasAstBeenChanged = \false;
        foreach ($items->items as $fieldValue) {
            if (!$fieldValue instanceof ArrayItem) {
                continue;
            }
            if (null === $fieldValue->key) {
                continue;
            }
            $fieldName = $this->valueResolver->getValue($fieldValue->key);
            if (null === $fieldName) {
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
                foreach ($configValue->value->items as $configItemValue) {
                    if (!$configItemValue instanceof ArrayItem) {
                        continue;
                    }
                    if (null === $configItemValue->key) {
                        continue;
                    }
                    if (!$this->valueResolver->isValue($configItemValue->key, 'wizards')) {
                        continue;
                    }
                    if (!$configItemValue->value instanceof Array_) {
                        continue;
                    }
                    foreach ($configItemValue->value->items as $wizardItemValue) {
                        if (null === $wizardItemValue) {
                            continue;
                        }
                        if (!$wizardItemValue->value instanceof Array_) {
                            continue;
                        }
                        if (null === $wizardItemValue->key) {
                            continue;
                        }
                        foreach ($wizardItemValue->value->items as $wizardItemSubValue) {
                            if (!$wizardItemSubValue instanceof ArrayItem) {
                                continue;
                            }
                            if (null === $wizardItemSubValue->key) {
                                continue;
                            }
                            if ($this->valueResolver->isValue($wizardItemSubValue->key, 'icon') && $this->valueResolver->isValues($wizardItemSubValue->value, $oldFileNames)) {
                                $wizardItemSubValue->value = new String_($this->oldToNewFileLocations[$this->valueResolver->getValue($wizardItemSubValue->value)]);
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
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        $oldToNewFileLocations = $configuration[self::OLD_TO_NEW_FILE_LOCATIONS] ?? $configuration;
        Assert::isArray($oldToNewFileLocations);
        Assert::allString(\array_keys($oldToNewFileLocations));
        Assert::allString($oldToNewFileLocations);
        $this->oldToNewFileLocations = $oldToNewFileLocations;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('The TCA migration migrates the icon calls to the new output if used as wizard icon', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
return [
    'ctrl' => [
    ],
    'columns' => [
        'bodytext' => [
            'config' => [
                'type' => 'text',
                'wizards' => [
                    't3editorHtml' => [
                        'icon' => 'wizard_table.gif',
                    ],
                ],
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
        'bodytext' => [
            'config' => [
                'type' => 'text',
                'wizards' => [
                    't3editorHtml' => [
                        'icon' => 'content-table',
                    ],
                ],
            ],
        ],
    ],
];
CODE_SAMPLE
, [self::OLD_TO_NEW_FILE_LOCATIONS => ['add.gif' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_add.gif']])]);
    }
}
