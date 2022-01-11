<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v3;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.3/Deprecation-94165-SysLanguageDatabaseTable.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v3\UseLanguageTypeForLanguageFieldColumnRector\UseLanguageTypeForLanguageFieldColumnRectorTest
 */
final class UseLanguageTypeForLanguageFieldColumnRector extends \Ssch\TYPO3Rector\Rector\Tca\AbstractTcaRector
{
    /**
     * @var string|null
     */
    private $languageField;
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('use the new TCA type language instead of foreign_table => sys_language for selecting a records', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
return [
    'ctrl' => [
        'languageField' => 'sys_language_uid',
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'Language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'eval' => 'int',
                'items' => [
                    [$_LLL_general . ':LGL.allLanguages', -1],
                    [$_LLL_general . ':LGL.default_value', 0]

                ],
            ],
        ],
    ],
];
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    'ctrl' => [
        'languageField' => 'sys_language_uid',
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'Language',
            'config' => [
                'type' => 'language'
            ],
        ],
    ],
];
CODE_SAMPLE
)]);
    }
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $this->hasAstBeenChanged = \false;
        if (!$this->isFullTcaDefinition($node)) {
            return null;
        }
        $ctrl = $this->extractSubArrayByKey($node, 'ctrl');
        if (!$ctrl instanceof \PhpParser\Node\Expr\Array_) {
            return null;
        }
        $value = $this->extractArrayValueByKey($ctrl, 'languageField');
        if (!$value instanceof \PhpParser\Node\Scalar\String_) {
            return null;
        }
        $this->languageField = $this->valueResolver->getValue($value);
        if (null === $this->languageField) {
            return null;
        }
        // we found a tca definition of a full table. Process it as a whole:
        $columns = $this->extractSubArrayByKey($node, 'columns');
        if (null !== $columns) {
            $this->refactorColumnList($columns);
        }
        return $this->hasAstBeenChanged ? $node : null;
    }
    protected function refactorColumn(\PhpParser\Node\Expr $columnName, \PhpParser\Node\Expr $columnTca) : void
    {
        $column = $this->valueResolver->getValue($columnName);
        if ($column !== $this->languageField) {
            return;
        }
        $configuration = $this->extractArrayItemByKey($columnTca, 'config');
        if (!$configuration instanceof \PhpParser\Node\Expr\ArrayItem) {
            return;
        }
        $foreignTable = $this->extractArrayItemByKey($configuration->value, 'foreign_table');
        if (!$foreignTable instanceof \PhpParser\Node\Expr\ArrayItem) {
            return;
        }
        if (!$this->valueResolver->isValue($foreignTable->value, 'sys_language')) {
            return;
        }
        $configuration->value = $this->nodeFactory->createArray(['type' => 'language']);
        $this->hasAstBeenChanged = \true;
    }
}
