<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
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
use Rector\Core\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Core\NodeManipulator\StmtsManipulator;
use Rector\Core\Rector\AbstractRector;
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
     * @readonly
     * @var \Rector\Core\NodeManipulator\StmtsManipulator
     */
    private $stmtsManipulator;
    /**
     * @var array<class-string<Stmt>>
     */
    private const NODE_TYPES = [Foreach_::class, Static_::class, Echo_::class, Return_::class, Expression::class, Throw_::class, If_::class, While_::class, Switch_::class, Nop::class];
    public function __construct(StmtsManipulator $stmtsManipulator)
    {
        $this->stmtsManipulator = $stmtsManipulator;
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $hasChanged = \false;
        $extractValues = [];
        foreach ($node->stmts as $key => $stmt) {
            if ($stmt instanceof Expression && $stmt->expr instanceof FuncCall && $this->isName($stmt->expr, 'extract') && !$stmt->expr->isFirstClassCallable()) {
                $appendExtractValues = $this->valueResolver->getValue($stmt->expr->getArgs()[0]->value);
                if (!\is_array($appendExtractValues)) {
                    // nothing can do as value is dynamic
                    break;
                }
                $extractValues = \array_merge($extractValues, \array_keys($appendExtractValues));
                continue;
            }
            if ($this->shouldSkip($node, $key, $stmt, $extractValues)) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($stmt);
            $varTagValueNode = $phpDocInfo->getVarTagValueNode();
            if (!$varTagValueNode instanceof VarTagValueNode) {
                continue;
            }
            if ($this->isObjectShapePseudoType($varTagValueNode)) {
                continue;
            }
            $variableName = \ltrim($varTagValueNode->variableName, '$');
            if ($variableName === '' && $this->isAnnotatableReturn($stmt)) {
                continue;
            }
            if ($this->hasVariableName($stmt, $variableName)) {
                continue;
            }
            $comments = $node->getComments();
            if (isset($comments[1])) {
                // skip edge case with double comment, as impossible to resolve by PHPStan doc parser
                continue;
            }
            $phpDocInfo->removeByType(VarTagValueNode::class);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param string[] $extractValues
     */
    private function shouldSkip(StmtsAwareInterface $stmtsAware, int $key, Stmt $stmt, array $extractValues) : bool
    {
        if (!\in_array(\get_class($stmt), self::NODE_TYPES, \true)) {
            return \true;
        }
        if (\count($stmt->getComments()) !== 1) {
            return \true;
        }
        foreach ($extractValues as $extractValue) {
            if ($this->stmtsManipulator->isVariableUsedInNextStmt($stmtsAware, $key + 1, $extractValue)) {
                return \true;
            }
        }
        return \false;
    }
    private function hasVariableName(Stmt $stmt, string $variableName) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($stmt, function (Node $node) use($variableName) : bool {
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
    private function isAnnotatableReturn(Stmt $stmt) : bool
    {
        return $stmt instanceof Return_ && $stmt->expr instanceof CallLike && !$stmt->expr instanceof New_;
    }
}
