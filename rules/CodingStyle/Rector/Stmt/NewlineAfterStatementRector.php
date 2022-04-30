<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Stmt;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Catch_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\Else_;
use PhpParser\Node\Stmt\ElseIf_;
use PhpParser\Node\Stmt\Finally_;
use PhpParser\Node\Stmt\For_;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\TryCatch;
use PhpParser\Node\Stmt\While_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Stmt\NewlineAfterStatementRector\NewlineAfterStatementRectorTest
 */
final class NewlineAfterStatementRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<class-string<Node>>
     */
    private const STMTS_TO_HAVE_NEXT_NEWLINE = [\PhpParser\Node\Stmt\ClassMethod::class, \PhpParser\Node\Stmt\Function_::class, \PhpParser\Node\Stmt\Property::class, \PhpParser\Node\Stmt\If_::class, \PhpParser\Node\Stmt\Foreach_::class, \PhpParser\Node\Stmt\Do_::class, \PhpParser\Node\Stmt\While_::class, \PhpParser\Node\Stmt\For_::class, \PhpParser\Node\Stmt\ClassConst::class, \PhpParser\Node\Stmt\Namespace_::class, \PhpParser\Node\Stmt\TryCatch::class, \PhpParser\Node\Stmt\Class_::class, \PhpParser\Node\Stmt\Trait_::class, \PhpParser\Node\Stmt\Interface_::class, \PhpParser\Node\Stmt\Switch_::class];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add new line after statements to tidify code', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function test()
    {
    }
    public function test2()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function test()
    {
    }

    public function test2()
    {
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
        return [\PhpParser\Node\Stmt::class];
    }
    /**
     * @param Stmt $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!\in_array(\get_class($node), self::STMTS_TO_HAVE_NEXT_NEWLINE, \true)) {
            return null;
        }
        $nextNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::NEXT_NODE);
        if (!$nextNode instanceof \PhpParser\Node) {
            return null;
        }
        if ($this->shouldSkip($nextNode)) {
            return null;
        }
        $endLine = $node->getEndLine();
        $line = $nextNode->getLine();
        $rangeLine = $line - $endLine;
        if ($rangeLine > 1) {
            /** @var Comment[]|null $comments */
            $comments = $nextNode->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS);
            if ($this->hasNoComment($comments)) {
                return null;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($nextNode);
            if ($phpDocInfo->hasChanged()) {
                return null;
            }
            /** @var Comment[] $comments */
            $line = $comments[0]->getLine();
            $rangeLine = $line - $endLine;
            if ($rangeLine > 1) {
                return null;
            }
        }
        $this->nodesToAddCollector->addNodeAfterNode(new \PhpParser\Node\Stmt\Nop(), $node);
        return $node;
    }
    /**
     * @param Comment[]|null $comments
     */
    private function hasNoComment(?array $comments) : bool
    {
        if ($comments === null) {
            return \true;
        }
        return !isset($comments[0]);
    }
    private function shouldSkip(?\PhpParser\Node $nextNode) : bool
    {
        if (!$nextNode instanceof \PhpParser\Node\Stmt) {
            return \true;
        }
        return \in_array(\get_class($nextNode), [\PhpParser\Node\Stmt\Else_::class, \PhpParser\Node\Stmt\ElseIf_::class, \PhpParser\Node\Stmt\Catch_::class, \PhpParser\Node\Stmt\Finally_::class], \true);
    }
}
