<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v7\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/7.0/Breaking-62833-Dividers2Tabs.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v7\v0\RemoveDivider2TabsConfigurationRector\RemoveDivider2TabsConfigurationRectorTest
 */
final class RemoveDivider2TabsConfigurationRector extends \Rector\Core\Rector\AbstractRector
{
    use TcaHelperTrait;
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Removed dividers2tabs functionality', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
return [
    'ctrl' => [
        'dividers2tabs' => true,
        'label' => 'complete_identifier',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
    ],
    'columns' => [
    ],
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    'ctrl' => [
        'label' => 'complete_identifier',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
    ],
    'columns' => [
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
     * @param \PhpParser\Node $node
     */
    public function refactor($node) : ?\PhpParser\Node
    {
        if (!$this->isFullTca($node)) {
            return null;
        }
        $ctrl = $this->extractCtrl($node);
        if (!$ctrl instanceof \PhpParser\Node\Expr\ArrayItem) {
            return null;
        }
        $ctrlItems = $ctrl->value;
        if (!$ctrlItems instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        foreach ($ctrlItems->items as $fieldValue) {
            if (!$fieldValue instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if (null === $fieldValue->key) {
                continue;
            }
            if ($this->valueResolver->isValue($fieldValue->key, 'dividers2tabs')) {
                $this->removeNode($fieldValue);
                return $node;
            }
        }
        return null;
    }
}
