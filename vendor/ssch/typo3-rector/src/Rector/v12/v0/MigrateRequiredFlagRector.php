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
 * @changelog https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.0/Deprecation-97035-RequiredOptionInEvalKeyword.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v12\v0\MigrateRequiredFlagRector\MigrateRequiredFlagRectorTest
 */
final class MigrateRequiredFlagRector extends AbstractTcaRector
{
    use TcaHelperTrait;
    /**
     * @var string
     */
    private const REQUIRED = 'required';
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrate required flag', [new CodeSample(<<<'CODE_SAMPLE'
'required_column' => [
    'config' => [
        'eval' => 'trim,required',
    ],
],
CODE_SAMPLE
, <<<'CODE_SAMPLE'
'required_column' => [
    'config' => [
        'eval' => 'trim',
        'required' => true,
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
        if (!StringUtility::inList($value, self::REQUIRED)) {
            return;
        }
        $evalList = ArrayUtility::trimExplode(',', $value, \true);
        // Remove "required" from $evalList
        $evalList = \array_filter($evalList, static function (string $eval) {
            return self::REQUIRED !== $eval;
        });
        if ([] !== $evalList) {
            // Write back filtered 'eval'
            $evalArrayItem->value = new String_(\implode(',', $evalList));
        } else {
            // 'eval' is empty, remove whole configuration
            $this->removeNode($evalArrayItem);
        }
        // If required config exists already, remove it to avoid duplicate array items
        $requiredItemToRemove = $this->extractArrayItemByKey($configArray, self::REQUIRED);
        if ($requiredItemToRemove instanceof ArrayItem) {
            $this->removeNode($requiredItemToRemove);
        }
        $configArray->items[] = new ArrayItem(new ConstFetch(new Name('true')), new String_(self::REQUIRED));
        $this->hasAstBeenChanged = \true;
    }
}
