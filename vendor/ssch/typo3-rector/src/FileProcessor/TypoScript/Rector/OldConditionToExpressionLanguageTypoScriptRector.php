<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector;

use RectorPrefix20211231\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use LogicException;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\Conditions\TyposcriptConditionMatcher;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Feature-85829-ImplementSymfonyExpressionLanguageForTypoScriptConditions.html
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\TypoScript\Rector\OldConditionToExpressionLanguageRectorTest
 */
final class OldConditionToExpressionLanguageTypoScriptRector extends \Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector
{
    /**
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @var TyposcriptConditionMatcher[]
     */
    private $conditionMatchers = [];
    /**
     * @param TyposcriptConditionMatcher[] $conditionMatchers
     */
    public function __construct(\Rector\Core\Provider\CurrentFileProvider $currentFileProvider, array $conditionMatchers = [])
    {
        $this->currentFileProvider = $currentFileProvider;
        $this->conditionMatchers = $conditionMatchers;
    }
    public function enterNode(\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void
    {
        if (!$statement instanceof \RectorPrefix20211231\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement) {
            return;
        }
        \preg_match_all('#\\[(.*)]#imU', $statement->condition, $conditions, \PREG_SET_ORDER);
        \preg_match_all('#]\\s*(&&|\\|\\||AND|OR)#imU', $statement->condition, $operators, \PREG_SET_ORDER);
        $conditions = \array_filter($conditions);
        $operators = \array_filter($operators);
        $operators = \array_map(function (array $match) {
            return $match[1];
        }, $operators);
        $conditions = \array_map(function (array $match) {
            return $match[1];
        }, $conditions);
        $newConditions = [];
        $applied = \false;
        if (!\is_array($conditions)) {
            return;
        }
        foreach ($conditions as $condition) {
            foreach ($this->conditionMatchers as $conditionMatcher) {
                $condition = \trim($condition);
                if (!$conditionMatcher->shouldApply($condition)) {
                    continue;
                }
                $changedCondition = $conditionMatcher->change($condition);
                $applied = \true;
                if (null !== $changedCondition) {
                    $newConditions[] = $changedCondition;
                }
            }
        }
        if (!$applied) {
            return;
        }
        $file = $this->currentFileProvider->getFile();
        $this->hasChanged = \true;
        if ($file instanceof \Rector\Core\ValueObject\Application\File) {
            $file->addRectorClassWithLine(new \Rector\ChangesReporting\ValueObject\RectorWithLineChange($this, $statement->sourceLine));
        }
        if ([] === $newConditions) {
            $statement->condition = '';
            return;
        }
        if (1 === \count($newConditions)) {
            $statement->condition = \sprintf('[%s]', $newConditions[0]);
            return;
        }
        if ([] === $operators) {
            $statement->condition = \sprintf('[%s]', \implode(' || ', $newConditions));
            return;
        }
        if (\count($operators) !== \count($newConditions) - 1) {
            throw new \LogicException('The count of operators must be exactly one less than the count of conditions');
        }
        \array_unshift($operators, '');
        $newCondition = '';
        foreach ($newConditions as $key => $value) {
            $operator = $operators[$key];
            if ('' === $operator) {
                $newCondition .= $value;
                continue;
            }
            $newCondition .= \sprintf(' %s %s', $operator, $value);
        }
        $statement->condition = \sprintf('[%s]', $newCondition);
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert old conditions to Symfony Expression Language', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
[globalVar = TSFE:id=17, TSFE:id=24]
CODE_SAMPLE
, <<<'CODE_SAMPLE'
[getTSFE().id in [17,24]]
CODE_SAMPLE
)]);
    }
}
