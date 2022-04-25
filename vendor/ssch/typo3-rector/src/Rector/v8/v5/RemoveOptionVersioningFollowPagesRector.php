<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v5;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.5/Deprecation-78524-TCAOptionVersioning_followPagesRemoved.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v5\RemoveOptionVersioningFollowPagesRector\RemoveOptionVersioningFollowPagesRectorTest
 */
final class RemoveOptionVersioningFollowPagesRector extends \Rector\Core\Rector\AbstractRector
{
    use TcaHelperTrait;
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('TCA option versioning_followPages removed', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        $ctrlArrayItem = $this->extractCtrl($node);
        if (!$ctrlArrayItem instanceof \PhpParser\Node\Expr\ArrayItem) {
            return null;
        }
        $ctrlItems = $ctrlArrayItem->value;
        if (!$ctrlItems instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $hasAstBeenChanged = \false;
        foreach ($ctrlItems->items as $fieldValue) {
            if (!$fieldValue instanceof \PhpParser\Node\Expr\ArrayItem) {
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
}
