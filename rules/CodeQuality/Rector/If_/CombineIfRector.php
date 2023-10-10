<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\If_;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\BetterPhpDocParser\Comment\CommentsMerger;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\If_\CombineIfRector\CombineIfRectorTest
 */
final class CombineIfRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\Comment\CommentsMerger
     */
    private $commentsMerger;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(CommentsMerger $commentsMerger, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->commentsMerger = $commentsMerger;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Merges nested if statements', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if ($cond1) {
            if ($cond2) {
                return 'foo';
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
        if ($cond1 && $cond2) {
            return 'foo';
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
        if ($this->shouldSkip($node)) {
            return null;
        }
        /** @var If_ $subIf */
        $subIf = $node->stmts[0];
        if ($this->hasVarTag($subIf)) {
            return null;
        }
        $node->cond->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        $node->cond = new BooleanAnd($node->cond, $subIf->cond);
        $node->stmts = $subIf->stmts;
        $this->commentsMerger->keepComments($node, [$subIf]);
        return $node;
    }
    private function shouldSkip(If_ $if) : bool
    {
        if ($if->else instanceof Else_) {
            return \true;
        }
        if (\count($if->stmts) !== 1) {
            return \true;
        }
        if ($if->elseifs !== []) {
            return \true;
        }
        if (!$if->stmts[0] instanceof If_) {
            return \true;
        }
        if ($if->stmts[0]->else instanceof Else_) {
            return \true;
        }
        return (bool) $if->stmts[0]->elseifs;
    }
    private function hasVarTag(If_ $if) : bool
    {
        $subIfPhpDocInfo = $this->phpDocInfoFactory->createFromNode($if);
        if (!$subIfPhpDocInfo instanceof PhpDocInfo) {
            return \false;
        }
        return $subIfPhpDocInfo->getVarTagValueNode() instanceof VarTagValueNode;
    }
}
