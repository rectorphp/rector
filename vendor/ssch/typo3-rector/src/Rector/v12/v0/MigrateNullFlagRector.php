<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v12\v0;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use Ssch\TYPO3Rector\Helper\ArrayUtility;
use Ssch\TYPO3Rector\Helper\StringUtility;
use Ssch\TYPO3Rector\Helper\TcaHelperTrait;
use Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Deprecation-97384-TCAOptionNullable.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v12\v0\MigrateNullFlagRector\MigrateNullFlagRectorTest
 */
final class MigrateNullFlagRector extends AbstractTcaRector
{
    use TcaHelperTrait;
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrate null flag', [new CodeSample(<<<'CODE_SAMPLE'
'nullable_column' => [
    'config' => [
        'eval' => 'null',
    ],
],
CODE_SAMPLE
, <<<'CODE_SAMPLE'
'nullable_column' => [
    'config' => [
        'nullable' => true,
    ],
],
CODE_SAMPLE
)]);
    }
    protected function refactorColumn(Expr $columnName, Expr $columnTca) : void
    {
        $configArray = $this->extractSubArrayByKey($columnTca, self::CONFIG);
        if (!$configArray instanceof Array_) {
            return;
        }
        if (!$this->hasKey($configArray, 'eval')) {
            return;
        }
        $evalArrayItem = $this->extractArrayItemByKey($configArray, 'eval');
        if (!$evalArrayItem instanceof ArrayItem) {
            return;
        }
        /** @var String_ $evalStringNode */
        $evalStringNode = $evalArrayItem->value;
        $value = $evalStringNode->value;
        if (!StringUtility::inList($value, 'null')) {
            return;
        }
        $evalList = ArrayUtility::trimExplode(',', $value, \true);
        // Remove "null" from $evalList
        $evalList = \array_filter($evalList, static function (string $eval) {
            return 'null' !== $eval;
        });
        if ([] !== $evalList) {
            // Write back filtered 'eval'
            $evalArrayItem->value = new String_(\implode(',', $evalList));
        } else {
            // 'eval' is empty, remove whole configuration
            $this->removeNode($evalArrayItem);
        }
        // If nullable config exists already, remove it to avoid duplicate array items
        $nullableItemToRemove = $this->extractArrayItemByKey($configArray, 'nullable');
        if ($nullableItemToRemove instanceof ArrayItem) {
            $this->removeNode($nullableItemToRemove);
        }
        $configArray->items[] = new ArrayItem(new ConstFetch(new Name('true')), new String_('nullable'));
        $this->hasAstBeenChanged = \true;
    }
}
