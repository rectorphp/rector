<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Case_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Switch_;
use Rector\Rector\AbstractRector;
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
        $this->removeDuplicatedCases($node);
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    private function removeDuplicatedCases(Switch_ $switch) : void
    {
        $totalKeys = \count($switch->cases);
        foreach (\array_keys($switch->cases) as $key) {
            if (isset($switch->cases[$key - 1]) && $switch->cases[$key - 1]->stmts === []) {
                continue;
            }
            $nextCases = [];
            for ($jumpToKey = $key + 1; $jumpToKey < $totalKeys; ++$jumpToKey) {
                if (!isset($switch->cases[$jumpToKey])) {
                    continue;
                }
                if (!$this->areSwitchStmtsEqualsAndWithBreak($switch->cases[$key], $switch->cases[$jumpToKey])) {
                    continue;
                }
                $nextCase = $switch->cases[$jumpToKey];
                unset($switch->cases[$jumpToKey]);
                $nextCases[] = $nextCase;
                $this->hasChanged = \true;
            }
            if ($nextCases === []) {
                continue;
            }
            \array_splice($switch->cases, $key + 1, 0, $nextCases);
            for ($jumpToKey = $key; $jumpToKey < $key + \count($nextCases); ++$jumpToKey) {
                $switch->cases[$jumpToKey]->stmts = [];
            }
            $key += \count($nextCases);
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
