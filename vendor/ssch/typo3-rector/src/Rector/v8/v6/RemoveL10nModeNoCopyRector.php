<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v6;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.6/Breaking-79242-RemoveL10n_modeNoCopy.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v6\RemoveL10nModeNoCopyRector\RemoveL10nModeNoCopyRectorTest
 */
final class RemoveL10nModeNoCopyRector extends AbstractRector
{
    use TcaHelperTrait;
    /**
     * @var string
     */
    private const BEHAVIOUR = 'behaviour';
    /**
     * @var string
     */
    private const ALLOW_LANGUAGE_SYNCHRONIZATION = 'allowLanguageSynchronization';
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
            $addAllowLanguageSynchronization = \false;
            $configArray = $fieldValue->value;
            $newConfiguration = new ArrayItem($this->nodeFactory->createArray([self::BEHAVIOUR => [self::ALLOW_LANGUAGE_SYNCHRONIZATION => \true]]), new String_('config'));
            foreach ($fieldValue->value->items as $configValue) {
                if (null === $configValue) {
                    continue;
                }
                if (null === $configValue->key) {
                    continue;
                }
                if (!$this->valueResolver->isValues($configValue->key, ['l10n_mode', 'config'])) {
                    continue;
                }
                if ($this->valueResolver->isValue($configValue->value, 'mergeIfNotBlank')) {
                    $addAllowLanguageSynchronization = \true;
                    $this->removeNode($configValue);
                    $hasAstBeenChanged = \true;
                } elseif ($this->valueResolver->isValue($configValue->value, 'noCopy')) {
                    $this->removeNode($configValue);
                    $hasAstBeenChanged = \true;
                } elseif ($configValue->value instanceof Array_) {
                    $configArray = $configValue->value;
                    $newConfiguration = new ArrayItem($this->nodeFactory->createArray([self::BEHAVIOUR => [self::ALLOW_LANGUAGE_SYNCHRONIZATION => \true]]));
                    foreach ($configValue->value->items as $configItemValue) {
                        if (!$configItemValue instanceof ArrayItem) {
                            continue;
                        }
                        if (null === $configItemValue->key) {
                            continue;
                        }
                        if (!$configItemValue->value instanceof Array_) {
                            continue;
                        }
                        if (!$this->valueResolver->isValue($configItemValue->key, self::BEHAVIOUR)) {
                            continue;
                        }
                        $configArray = $configItemValue->value;
                        $newConfiguration = new ArrayItem($this->nodeFactory->createTrue(), new String_(self::ALLOW_LANGUAGE_SYNCHRONIZATION));
                        foreach ($configItemValue->value->items as $behaviourConfiguration) {
                            if (!$behaviourConfiguration instanceof ArrayItem) {
                                continue;
                            }
                            if (null === $behaviourConfiguration->key) {
                                continue;
                            }
                            if (!$this->valueResolver->isValue($behaviourConfiguration->key, self::ALLOW_LANGUAGE_SYNCHRONIZATION)) {
                                continue;
                            }
                            $addAllowLanguageSynchronization = \false;
                            if ('' === (string) $this->valueResolver->getValue($behaviourConfiguration->value)) {
                                $behaviourConfiguration->value = $this->nodeFactory->createTrue();
                                $hasAstBeenChanged = \true;
                            }
                        }
                    }
                } elseif ($configValue->value instanceof StaticCall) {
                    $addAllowLanguageSynchronization = \false;
                    break;
                }
            }
            if (!$addAllowLanguageSynchronization) {
                continue;
            }
            $configArray->items[] = $newConfiguration;
            $hasAstBeenChanged = \true;
        }
        return $hasAstBeenChanged ? $node : null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove l10n_mode noCopy', [new CodeSample(<<<'CODE_SAMPLE'
return [
    'ctrl' => [],
    'columns' => [
        'foo' => [
            'exclude' => 1,
            'l10n_mode' => 'mergeIfNotBlank',
            'label' => 'Bar',
        ],
    ],
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    'ctrl' => [],
    'columns' => [
        'foo' => [
            'exclude' => 1,
            'label' => 'Bar',
            'config' => [
                'behaviour' => [
                    'allowLanguageSynchronization' => true
                ]
            ],
        ],
    ],
];
CODE_SAMPLE
)]);
    }
}
