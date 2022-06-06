<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v5;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\Array_;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.5/Deprecation-78524-TCAOptionVersioning_followPagesRemoved.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v5\RemoveOptionVersioningFollowPagesRector\RemoveOptionVersioningFollowPagesRectorTest
 */
final class RemoveOptionVersioningFollowPagesRector extends AbstractRector
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
        $ctrlArrayItem = $this->extractCtrl($node);
        if (!$ctrlArrayItem instanceof ArrayItem) {
            return null;
        }
        $ctrlItems = $ctrlArrayItem->value;
        if (!$ctrlItems instanceof Array_) {
            return null;
        }
        $hasAstBeenChanged = \false;
        foreach ($ctrlItems->items as $fieldValue) {
            if (!$fieldValue instanceof ArrayItem) {
                continue;
            }
            if (null === $fieldValue->key) {
                continue;
            }
            if ($this->valueResolver->isValue($fieldValue->key, 'versioning_followPages')) {
                $this->removeNode($fieldValue);
                $hasAstBeenChanged = \true;
            } elseif ($this->valueResolver->isValue($fieldValue->key, 'versioningWS')) {
                $versioningWS = $this->valueResolver->getValue($fieldValue->value);
                if (!\is_bool($versioningWS)) {
                    $fieldValue->value = (bool) $versioningWS ? $this->nodeFactory->createTrue() : $this->nodeFactory->createFalse();
                    $hasAstBeenChanged = \true;
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
        return new RuleDefinition('TCA option versioning_followPages removed', [new CodeSample(<<<'CODE_SAMPLE'
return [
    'ctrl' => [
        'versioningWS' => 2,
        'versioning_followPages' => TRUE,
    ],
    'columns' => [
    ]
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    'ctrl' => [
        'versioningWS' => true,
    ],
    'columns' => [
    ]
];
CODE_SAMPLE
)]);
    }
}
