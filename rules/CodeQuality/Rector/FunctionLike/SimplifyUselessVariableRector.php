<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FunctionLike;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\NodeAnalyzer\CallAnalyzer;
use Rector\NodeAnalyzer\VariableAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector\SimplifyUselessVariableRectorTest
 */
final class SimplifyUselessVariableRector extends AbstractRector
{
    /**
     * @readonly
     */
    private VariableAnalyzer $variableAnalyzer;
    /**
     * @readonly
     */
    private CallAnalyzer $callAnalyzer;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    public function __construct(VariableAnalyzer $variableAnalyzer, CallAnalyzer $callAnalyzer, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->variableAnalyzer = $variableAnalyzer;
        $this->callAnalyzer = $callAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove useless variable assigns', [new CodeSample(<<<'CODE_SAMPLE'
function () {
    $a = true;
    return $a;
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function () {
    return true;
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }
    /**
     * @param StmtsAware $node
     */
    public function refactor(Node $node): ?Node
    {
        $stmts = $node->stmts;
        if ($stmts === null) {
            return null;
        }
        foreach ($stmts as $key => $stmt) {
            // has previous node?
            if (!isset($stmts[$key - 1])) {
                continue;
            }
            if (!$stmt instanceof Return_) {
                continue;
            }
            $previousStmt = $stmts[$key - 1];
            if ($this->shouldSkipStmt($stmt, $previousStmt)) {
                return null;
            }
            if ($this->hasSomeComment($previousStmt)) {
                return null;
            }
            // the variable might be used in commented-out code between assign and return
            if ($this->isVariableMentionedInComment($stmt)) {
                return null;
            }
            if ($this->isReturnWithVarAnnotation($stmt)) {
                return null;
            }
            if (!$previousStmt instanceof Expression) {
                return null;
            }
            $assign = $previousStmt->expr;
            if (!$assign instanceof Assign) {
                return null;
            }
            $stmt->expr = $assign->expr;
            unset($node->stmts[$key - 1]);
            return $node;
        }
        return null;
    }
    private function shouldSkipStmt(Return_ $return, Stmt $previousStmt): bool
    {
        if (!$return->expr instanceof Variable) {
            return \true;
        }
        if ($return->getAttribute(AttributeKey::IS_BYREF_RETURN) === \true) {
            return \true;
        }
        if (!$previousStmt instanceof Expression) {
            return \true;
        }
        // is variable part of single assign
        $previousNode = $previousStmt->expr;
        if (!$previousNode instanceof Assign) {
            return \true;
        }
        $variable = $return->expr;
        // is the same variable
        if (!$this->nodeComparator->areNodesEqual($previousNode->var, $variable)) {
            return \true;
        }
        if ($this->variableAnalyzer->isStaticOrGlobal($variable)) {
            return \true;
        }
        /** @var Variable $previousVar */
        $previousVar = $previousNode->var;
        if ($this->callAnalyzer->isNewInstance($previousVar)) {
            return \true;
        }
        return $this->variableAnalyzer->isUsedByReference($variable);
    }
    private function isVariableMentionedInComment(Return_ $return): bool
    {
        $comments = $return->getComments();
        if ($comments === []) {
            return \false;
        }
        if (!$return->expr instanceof Variable) {
            return \false;
        }
        $variableName = $return->expr->name;
        if (!is_string($variableName)) {
            return \false;
        }
        $found = \false;
        foreach ($comments as $comment) {
            if (strpos($comment->getText(), '$' . $variableName) !== \false) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    private function hasSomeComment(Stmt $stmt): bool
    {
        if ($stmt->getComments() !== []) {
            return \true;
        }
        return $stmt->getDocComment() instanceof Doc;
    }
    private function isReturnWithVarAnnotation(Return_ $return): bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($return);
        return !$phpDocInfo->getVarType() instanceof MixedType;
    }
}
