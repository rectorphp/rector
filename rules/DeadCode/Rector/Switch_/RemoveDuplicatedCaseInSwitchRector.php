<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector\RemoveDuplicatedCaseInSwitchRectorTest
 */
final class RemoveDuplicatedCaseInSwitchRector extends AbstractRector
{
    /**
     * @readonly
     */
    private BetterStandardPrinter $betterStandardPrinter;
    private bool $hasChanged = \false;
    public function __construct(BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('2 following switch keys with identical  will be reduced to one result', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        switch ($name) {
             case 'clearHeader':
                 return $this->modifyHeader($node, 'remove');
             case 'clearAllHeaders':
                 return $this->modifyHeader($node, 'replace');
             case 'clearRawHeaders':
                 return $this->modifyHeader($node, 'replace');
             case '...':
                 return 5;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        switch ($name) {
             case 'clearHeader':
                 return $this->modifyHeader($node, 'remove');
             case 'clearAllHeaders':
             case 'clearRawHeaders':
                 return $this->modifyHeader($node, 'replace');
             case '...':
                 return 5;
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Switch_::class];
    }
    /**
     * @param Switch_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (count($node->cases) < 2) {
            return null;
        }
        $this->hasChanged = \false;
        $this->removeDuplicatedCases($node);
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    private function removeDuplicatedCases(Switch_ $switch): void
    {
        /** @var Case_[] */
        $result = [];
        /** @var int[] */
        $processedCasesKeys = [];
        foreach ($switch->cases as $outerCaseKey => $outerCase) {
            if (in_array($outerCaseKey, $processedCasesKeys)) {
                continue;
            }
            $processedCasesKeys[] = $outerCaseKey;
            if ($outerCase->stmts === []) {
                $result[] = $outerCase;
                continue;
            }
            /** @var array<int, Case_> */
            $casesWithoutStmts = [];
            /** @var Case_[] */
            $equalCases = [];
            foreach ($switch->cases as $innerCaseKey => $innerCase) {
                if (in_array($innerCaseKey, $processedCasesKeys)) {
                    continue;
                }
                if ($innerCase->stmts === []) {
                    $casesWithoutStmts[$innerCaseKey] = $innerCase;
                    continue;
                }
                if ($this->areSwitchStmtsEqualsAndWithBreak($outerCase, $innerCase)) {
                    foreach ($casesWithoutStmts as $caseWithoutStmtsKey => $caseWithoutStmts) {
                        $equalCases[] = $caseWithoutStmts;
                        $processedCasesKeys[] = $caseWithoutStmtsKey;
                    }
                    $innerCase->stmts = [];
                    $equalCases[] = $innerCase;
                    $processedCasesKeys[] = $innerCaseKey;
                }
                $casesWithoutStmts = [];
            }
            if ($equalCases === []) {
                $result[] = $outerCase;
                continue;
            }
            $this->hasChanged = \true;
            $equalCases[array_key_last($equalCases)]->stmts = $outerCase->stmts;
            $outerCase->stmts = [];
            $result = array_merge($result, array_merge([$outerCase], $equalCases));
        }
        $switch->cases = $result;
    }
    private function areSwitchStmtsEqualsAndWithBreak(Case_ $currentCase, Case_ $nextCase): bool
    {
        /**
         * Skip multi no stmts
         * @see rules-tests/DeadCode/Rector/Switch_/RemoveDuplicatedCaseInSwitchRector/Fixture/skip_multi_no_stmts.php.inc
         */
        if ($currentCase->stmts[0] instanceof Break_ && $nextCase->stmts[0] instanceof Break_) {
            return $this->areSwitchStmtsEqualsConsideringComments($currentCase, $nextCase);
        }
        if (!$this->nodeComparator->areNodesEqual($currentCase->stmts, $nextCase->stmts)) {
            return \false;
        }
        foreach ($currentCase->stmts as $stmt) {
            if ($stmt instanceof Break_ || $stmt instanceof Return_) {
                return \true;
            }
        }
        return \false;
    }
    private function areSwitchStmtsEqualsConsideringComments(Case_ $currentCase, Case_ $nextCase): bool
    {
        $currentCasePrintResult = $this->betterStandardPrinter->print($currentCase->stmts);
        $nextCasePrintResult = $this->betterStandardPrinter->print($nextCase->stmts);
        return $currentCasePrintResult === $nextCasePrintResult;
    }
}
