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
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\MultiInstanceofChecker;
use Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector\RemoveNonExistingVarAnnotationRectorTest
 *
 * @changelog https://github.com/phpstan/phpstan/commit/d17e459fd9b45129c5deafe12bca56f30ea5ee99#diff-9f3541876405623b0d18631259763dc1
 */
final class RemoveNonExistingVarAnnotationRector extends AbstractRector
{
    /**
     * @var array<class-string<Node>>
     */
    private const NODES_TO_MATCH = [Assign::class, AssignRef::class, Foreach_::class, Static_::class, Echo_::class, Return_::class, Expression::class, Throw_::class, If_::class, While_::class, Switch_::class, Nop::class];
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer
     */
    private $exprUsedInNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\Util\MultiInstanceofChecker
     */
    private $multiInstanceofChecker;
    public function __construct(ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer, MultiInstanceofChecker $multiInstanceofChecker)
    {
        $this->exprUsedInNodeAnalyzer = $exprUsedInNodeAnalyzer;
        $this->multiInstanceofChecker = $multiInstanceofChecker;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Removes non-existing @var annotations above the code', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Node::class];
    }
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return null;
        }
        if ($this->isObjectShapePseudoType($varTagValueNode)) {
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
            // skip edge case with double comment, as impossible to resolve by PHPStan doc parser
            return null;
        }
        $phpDocInfo->removeByType(VarTagValueNode::class);
        return $node;
    }
    private function isUsedInNextNodeWithExtractPreviouslyCalled(Node $node, string $variableName) : bool
    {
        $variable = new Variable($variableName);
        $isUsedInNextNode = (bool) $this->betterNodeFinder->findFirstNext($node, function (Node $node) use($variable) : bool {
            return $this->exprUsedInNodeAnalyzer->isUsed($node, $variable);
        });
        if (!$isUsedInNextNode) {
            return \false;
        }
        return (bool) $this->betterNodeFinder->findFirstPrevious($node, function (Node $subNode) : bool {
            if (!$subNode instanceof FuncCall) {
                return \false;
            }
            return $this->nodeNameResolver->isName($subNode, 'extract');
        });
    }
    private function shouldSkip(Node $node) : bool
    {
        if (!$node instanceof Nop) {
            return !$this->multiInstanceofChecker->isInstanceOf($node, self::NODES_TO_MATCH);
        }
        if (\count($node->getComments()) <= 1) {
            return !$this->multiInstanceofChecker->isInstanceOf($node, self::NODES_TO_MATCH);
        }
        return \true;
    }
    private function hasVariableName(Node $node, string $variableName) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($node, function (Node $node) use($variableName) : bool {
            if (!$node instanceof Variable) {
                return \false;
            }
            return $this->isName($node, $variableName);
        });
    }
    /**
     * This is a hack,
     * that waits on phpdoc-parser to get merged - https://github.com/phpstan/phpdoc-parser/pull/145
     */
    private function isObjectShapePseudoType(VarTagValueNode $varTagValueNode) : bool
    {
        if (!$varTagValueNode->type instanceof IdentifierTypeNode) {
            return \false;
        }
        if ($varTagValueNode->type->name !== 'object') {
            return \false;
        }
        if (\strncmp($varTagValueNode->description, '{', \strlen('{')) !== 0) {
            return \false;
        }
        return \strpos($varTagValueNode->description, '}') !== \false;
    }
}
