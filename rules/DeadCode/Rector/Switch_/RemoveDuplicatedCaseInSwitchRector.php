<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector\RemoveDuplicatedCaseInSwitchRectorTest
 */
final class RemoveDuplicatedCaseInSwitchRector extends AbstractRector
{
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function getRuleDefinition() : RuleDefinition
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
    public function getNodeTypes() : array
    {
        return [Switch_::class];
    }
    /**
     * @param Switch_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (\count($node->cases) < 2) {
            return null;
        }
        $this->hasChanged = \false;
        $insertByKeys = $this->resolveInsertedByKeys($node);
        $this->insertCaseByKeys($node, $insertByKeys);
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @return array<int, array<int, Case_>>
     */
    private function resolveInsertedByKeys(Switch_ $switch) : array
    {
        $totalKeys = \count($switch->cases);
        $insertByKeys = [];
        $appendKey = 0;
        foreach ($switch->cases as $key => $case) {
            if ($case->stmts === []) {
                continue;
            }
            for ($jumpToKey = $key + 2; $jumpToKey < $totalKeys; ++$jumpToKey) {
                if (!isset($switch->cases[$jumpToKey])) {
                    continue;
                }
                if (!$this->areSwitchStmtsEqualsAndWithBreak($case, $switch->cases[$jumpToKey])) {
                    continue;
                }
                $nextCase = $switch->cases[$jumpToKey];
                unset($switch->cases[$jumpToKey]);
                $insertByKeys[$key + $appendKey][] = $nextCase;
                $this->hasChanged = \true;
            }
            $appendKey = isset($insertByKeys[$key]) ? \count($insertByKeys[$key]) : 0;
        }
        return $insertByKeys;
    }
    /**
     * @param array<int, array<int, Case_>> $insertByKeys
     */
    private function insertCaseByKeys(Switch_ $switch, array $insertByKeys) : void
    {
        foreach ($insertByKeys as $key => $insertByKey) {
            $nextKey = $key + 1;
            \array_splice($switch->cases, $nextKey, 0, $insertByKey);
        }
        /** @var Case_|null $previousCase */
        $previousCase = null;
        foreach ($switch->cases as $case) {
            if ($previousCase instanceof Case_ && $this->areSwitchStmtsEqualsAndWithBreak($case, $previousCase)) {
                $previousCase->stmts = [];
                $this->hasChanged = \true;
            }
            $previousCase = $case;
        }
    }
    private function areSwitchStmtsEqualsAndWithBreak(Case_ $currentCase, Case_ $nextCase) : bool
    {
        if (!$this->nodeComparator->areNodesEqual($currentCase->stmts, $nextCase->stmts)) {
            return \false;
        }
        foreach ($currentCase->stmts as $stmt) {
            if ($stmt instanceof Break_) {
                return \true;
            }
            if ($stmt instanceof Return_) {
                return \true;
            }
        }
        return \false;
    }
}
