<?php

declare (strict_types=1);
namespace Rector\Php52\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Break_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Continue_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\While_;
use PhpParser\NodeVisitor;
use PHPStan\Type\Constant\ConstantIntegerType;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php52\Rector\Switch_\ContinueToBreakInSwitchRector\ContinueToBreakInSwitchRectorTest
 */
final class ContinueToBreakInSwitchRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    private bool $hasChanged = \false;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::CONTINUE_TO_BREAK;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use `break` instead of `continue` in switch statements', [new CodeSample(<<<'CODE_SAMPLE'
function some_run($value)
{
    switch ($value) {
        case 1:
            echo 'Hi';
            continue;
        case 2:
            echo 'Hello';
            break;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function some_run($value)
{
    switch ($value) {
        case 1:
            echo 'Hi';
            break;
        case 2:
            echo 'Hello';
            break;
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
    public function refactor(Node $node) : ?Switch_
    {
        $this->hasChanged = \false;
        foreach ($node->cases as $case) {
            $this->processContinueStatement($case);
        }
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param \PhpParser\Node\Stmt|\Rector\Contract\PhpParser\Node\StmtsAwareInterface $stmt
     */
    private function processContinueStatement($stmt) : void
    {
        $this->traverseNodesWithCallable($stmt, function (Node $subNode) {
            if ($subNode instanceof Class_ || $subNode instanceof Function_ || $subNode instanceof Closure) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            // continue is belong to loop
            if ($subNode instanceof Foreach_ || $subNode instanceof While_ || $subNode instanceof Do_ || $subNode instanceof For_) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$subNode instanceof Continue_) {
                return null;
            }
            if (!$subNode->num instanceof Expr) {
                $this->hasChanged = \true;
                return new Break_();
            }
            if ($subNode->num instanceof Int_) {
                $continueNumber = $this->valueResolver->getValue($subNode->num);
                if ($continueNumber <= 1) {
                    $this->hasChanged = \true;
                    return new Break_();
                }
            } elseif ($subNode->num instanceof Variable) {
                $processVariableNum = $this->processVariableNum($subNode, $subNode->num);
                if ($processVariableNum instanceof Break_) {
                    $this->hasChanged = \true;
                    return $processVariableNum;
                }
            }
            return null;
        });
    }
    /**
     * @return \PhpParser\Node\Stmt\Continue_|\PhpParser\Node\Stmt\Break_
     */
    private function processVariableNum(Continue_ $continue, Variable $numVariable)
    {
        $staticType = $this->getType($numVariable);
        if (!$staticType->isConstantValue()->yes()) {
            return $continue;
        }
        if (!$staticType instanceof ConstantIntegerType) {
            return $continue;
        }
        if ($staticType->getValue() > 1) {
            return $continue;
        }
        return new Break_();
    }
}
