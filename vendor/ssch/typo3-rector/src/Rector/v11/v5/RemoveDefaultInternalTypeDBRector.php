<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v5;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.5/Important-95384-TCAInternal_typedbOptionalForTypegroup.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v5\RemoveDefaultInternalTypeDBRector\RemoveDefaultInternalTypeDBRectorTest
 */
final class RemoveDefaultInternalTypeDBRector extends \Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector
{
    use TcaHelperTrait;
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove the default type for internal_type', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
return [
    'ctrl' => [
    ],
    'columns' => [
        'foobar' => [
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
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
        'foobar' => [
            'config' => [
                'type' => 'group',
            ],
        ],
    ],
];
CODE_SAMPLE
)]);
    }
    protected function refactorColumn(\PhpParser\Node\Expr $columnName, \PhpParser\Node\Expr $columnTca) : void
    {
        $config = $this->extractSubArrayByKey($columnTca, self::CONFIG);
        if (!$config instanceof \PhpParser\Node\Expr\Array_) {
            return;
        }
        if (!$this->configIsOfInternalType($config, 'db')) {
            return;
        }
        $nodeToRemove = $this->extractArrayItemByKey($config, 'internal_type');
        if (null !== $nodeToRemove) {
            $this->removeNode($nodeToRemove);
            $this->hasAstBeenChanged = \true;
        }
    }
}
