<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Static_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Stmt\While_;
use PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Rector\Comments\CommentRemover;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer;
use RectorPrefix20220418\Symplify\PackageBuilder\Php\TypeChecker;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector\RemoveNonExistingVarAnnotationRectorTest
 *
 * @changelog https://github.com/phpstan/phpstan/commit/d17e459fd9b45129c5deafe12bca56f30ea5ee99#diff-9f3541876405623b0d18631259763dc1
 */
final class RemoveNonExistingVarAnnotationRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<class-string<Node>>
     */
    private const NODES_TO_MATCH = [\PhpParser\Node\Expr\Assign::class, \PhpParser\Node\Expr\AssignRef::class, \PhpParser\Node\Stmt\Foreach_::class, \PhpParser\Node\Stmt\Static_::class, \PhpParser\Node\Stmt\Echo_::class, \PhpParser\Node\Stmt\Return_::class, \PhpParser\Node\Stmt\Expression::class, \PhpParser\Node\Stmt\Throw_::class, \PhpParser\Node\Stmt\If_::class, \PhpParser\Node\Stmt\While_::class, \PhpParser\Node\Stmt\Switch_::class, \PhpParser\Node\Stmt\Nop::class];
    /**
     * @readonly
     * @var \Symplify\PackageBuilder\Php\TypeChecker
     */
    private $typeChecker;
    /**
     * @readonly
     * @var \Rector\Comments\CommentRemover
     */
    private $commentRemover;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer
     */
    private $exprUsedInNodeAnalyzer;
    public function __construct(\RectorPrefix20220418\Symplify\PackageBuilder\Php\TypeChecker $typeChecker, \Rector\Comments\CommentRemover $commentRemover, \Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer)
    {
        $this->typeChecker = $typeChecker;
        $this->commentRemover = $commentRemover;
        $this->exprUsedInNodeAnalyzer = $exprUsedInNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Removes non-existing @var annotations above the code', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function get()
    {
        /** @var Training[] $trainings */
        return $this->getData();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function get()
    {
        return $this->getData();
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
        return [\PhpParser\Node::class];
    }
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode) {
            return null;
        }
        $variableName = \ltrim($varTagValueNode->variableName, '$');
        if ($this->hasVariableName($node, $variableName)) {
            return null;
        }
        if ($this->isUsedInNextNodeWithExtractPreviouslyCalled($node, $variableName)) {
            return null;
        }
        $comments = $node->getComments();
        if (isset($comments[1])) {
            $this->commentRemover->rollbackComments($node, $comments[1]);
            return $node;
        }
        $phpDocInfo->removeByType(\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode::class);
        return $node;
    }
    private function isUsedInNextNodeWithExtractPreviouslyCalled(\PhpParser\Node $node, string $variableName) : bool
    {
        $variable = new \PhpParser\Node\Expr\Variable($variableName);
        $isUsedInNextNode = (bool) $this->betterNodeFinder->findFirstNext($node, function (\PhpParser\Node $node) use($variable) : bool {
            return $this->exprUsedInNodeAnalyzer->isUsed($node, $variable);
        });
        if (!$isUsedInNextNode) {
            return \false;
        }
        return (bool) $this->betterNodeFinder->findFirstPreviousOfNode($node, function (\PhpParser\Node $subNode) : bool {
            if (!$subNode instanceof \PhpParser\Node\Expr\FuncCall) {
                return \false;
            }
            return $this->nodeNameResolver->isName($subNode, 'extract');
        });
    }
    private function shouldSkip(\PhpParser\Node $node) : bool
    {
        if (!$node instanceof \PhpParser\Node\Stmt\Nop) {
            return !$this->typeChecker->isInstanceOf($node, self::NODES_TO_MATCH);
        }
        if (\count($node->getComments()) <= 1) {
            return !$this->typeChecker->isInstanceOf($node, self::NODES_TO_MATCH);
        }
        return \true;
    }
    private function hasVariableName(\PhpParser\Node $node, string $variableName) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($node, function (\PhpParser\Node $node) use($variableName) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\Variable) {
                return \false;
            }
            return $this->isName($node, $variableName);
        });
    }
}
