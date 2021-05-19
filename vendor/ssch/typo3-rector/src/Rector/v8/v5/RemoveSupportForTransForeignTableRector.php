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
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.5/Breaking-78191-RemoveSupportForTransForeignTableInTCA.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v5\RemoveSupportForTransForeignTableRector\RemoveSupportForTransForeignTableRectorTest
 */
final class RemoveSupportForTransForeignTableRector extends \Rector\Core\Rector\AbstractRector
{
    use TcaHelperTrait;
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
        $ctrl = $this->extractCtrl($node);
        if (!$ctrl instanceof \PhpParser\Node\Expr\ArrayItem) {
            return null;
        }
        $items = $ctrl->value;
        if (!$items instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $hasAstBeenChanged = \false;
        foreach ($items->items as $fieldValue) {
            if (!$fieldValue instanceof \PhpParser\Node\Expr\ArrayItem) {
                continue;
            }
            if (null === $fieldValue->key) {
                continue;
            }
            if ($this->valueResolver->isValues($fieldValue->key, ['transForeignTable', 'transOrigPointerTable'])) {
                $this->removeNode($fieldValue);
                $hasAstBeenChanged = \true;
            }
        }
        return $hasAstBeenChanged ? $node : null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove support for transForeignTable in TCA', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
return [
    'ctrl' => [
        'transForeignTable' => 'l10n_parent',
        'transOrigPointerTable' => 'l10n_parent',
    ],
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    'ctrl' => [],
];
CODE_SAMPLE
)]);
    }
}
