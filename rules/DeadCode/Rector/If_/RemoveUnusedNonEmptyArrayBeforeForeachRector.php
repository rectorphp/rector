<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ArrayType;
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
final class RemoveUnusedNonEmptyArrayBeforeForeachRector extends AbstractRector
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
    public function __construct(CountManipulator $countManipulator, IfManipulator $ifManipulator, UselessIfCondBeforeForeachDetector $uselessIfCondBeforeForeachDetector, ReservedKeywordAnalyzer $reservedKeywordAnalyzer)
    {
        $this->countManipulator = $countManipulator;
        $this->ifManipulator = $ifManipulator;
        $this->uselessIfCondBeforeForeachDetector = $uselessIfCondBeforeForeachDetector;
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused if check to non-empty array before foreach of the array', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isUselessBeforeForeachCheck($node)) {
            return null;
        }
        $stmt = $node->stmts[0];
        $ifComments = $node->getAttribute(AttributeKey::COMMENTS) ?? [];
        $stmtComments = $stmt->getAttribute(AttributeKey::COMMENTS) ?? [];
        $comments = \array_merge($ifComments, $stmtComments);
        $stmt->setAttribute(AttributeKey::COMMENTS, $comments);
        return $stmt;
    }
    private function isUselessBeforeForeachCheck(If_ $if) : bool
    {
        if (!$this->ifManipulator->isIfWithOnly($if, Foreach_::class)) {
            return \false;
        }
        /** @var Foreach_ $foreach */
        $foreach = $if->stmts[0];
        $foreachExpr = $foreach->expr;
        if ($foreachExpr instanceof Variable) {
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
        // we know it's an array
        $condType = $this->getType($if->cond);
        if ($condType instanceof ArrayType) {
            return \true;
        }
        return $this->countManipulator->isCounterHigherThanOne($if->cond, $foreachExpr);
    }
}
