<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Php\ReservedKeywordAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeManipulator\CountManipulator;
use Rector\DeadCode\UselessIfCondBeforeForeachDetector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector\RemoveUnusedNonEmptyArrayBeforeForeachRectorTest
 */
final class RemoveUnusedNonEmptyArrayBeforeForeachRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeManipulator\CountManipulator
     */
    private $countManipulator;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\DeadCode\UselessIfCondBeforeForeachDetector
     */
    private $uselessIfCondBeforeForeachDetector;
    /**
     * @readonly
     * @var \Rector\Core\Php\ReservedKeywordAnalyzer
     */
    private $reservedKeywordAnalyzer;
    public function __construct(\Rector\DeadCode\NodeManipulator\CountManipulator $countManipulator, \Rector\Core\NodeManipulator\IfManipulator $ifManipulator, \Rector\DeadCode\UselessIfCondBeforeForeachDetector $uselessIfCondBeforeForeachDetector, \Rector\Core\Php\ReservedKeywordAnalyzer $reservedKeywordAnalyzer)
    {
        $this->countManipulator = $countManipulator;
        $this->ifManipulator = $ifManipulator;
        $this->uselessIfCondBeforeForeachDetector = $uselessIfCondBeforeForeachDetector;
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove unused if check to non-empty array before foreach of the array', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $values = [];
        if ($values !== []) {
            foreach ($values as $value) {
                echo $value;
            }
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $values = [];
        foreach ($values as $value) {
            echo $value;
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
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isUselessBeforeForeachCheck($node)) {
            return null;
        }
        $stmt = $node->stmts[0];
        $ifComments = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS) ?? [];
        $stmtComments = $stmt->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS) ?? [];
        $comments = \array_merge($ifComments, $stmtComments);
        $stmt->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS, $comments);
        return $stmt;
    }
    private function isUselessBeforeForeachCheck(\PhpParser\Node\Stmt\If_ $if) : bool
    {
        if (!$this->ifManipulator->isIfWithOnly($if, \PhpParser\Node\Stmt\Foreach_::class)) {
            return \false;
        }
        /** @var Foreach_ $foreach */
        $foreach = $if->stmts[0];
        $foreachExpr = $foreach->expr;
        if ($foreachExpr instanceof \PhpParser\Node\Expr\Variable) {
            $variableName = $this->nodeNameResolver->getName($foreachExpr);
            if (\is_string($variableName) && $this->reservedKeywordAnalyzer->isNativeVariable($variableName)) {
                return \false;
            }
        }
        if ($this->uselessIfCondBeforeForeachDetector->isMatchingNotIdenticalEmptyArray($if, $foreachExpr)) {
            return \true;
        }
        if ($this->uselessIfCondBeforeForeachDetector->isMatchingNotEmpty($if, $foreachExpr)) {
            return \true;
        }
        return $this->countManipulator->isCounterHigherThanOne($if->cond, $foreachExpr);
    }
}
