<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\FunctionLike;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\NodeAnalyzer\CallAnalyzer;
use Rector\NodeAnalyzer\VariableAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\AssignAndBinaryMap;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector\SimplifyUselessVariableRectorTest
 */
final class SimplifyUselessVariableRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     */
    private AssignAndBinaryMap $assignAndBinaryMap;
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
    /**
     * @api
     * @var string
     */
    public const ONLY_DIRECT_ASSIGN = 'only_direct_assign';
    private bool $onlyDirectAssign = \false;
    public function __construct(AssignAndBinaryMap $assignAndBinaryMap, VariableAnalyzer $variableAnalyzer, CallAnalyzer $callAnalyzer, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->assignAndBinaryMap = $assignAndBinaryMap;
        $this->variableAnalyzer = $variableAnalyzer;
        $this->callAnalyzer = $callAnalyzer;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function configure(array $configuration) : void
    {
        $this->onlyDirectAssign = $configuration[self::ONLY_DIRECT_ASSIGN] ?? \false;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove useless variable assigns', [new ConfiguredCodeSample(
            <<<'CODE_SAMPLE'
function () {
    $a = true;
    return $a;
};
CODE_SAMPLE
,
            <<<'CODE_SAMPLE'
function () {
    return true;
};
CODE_SAMPLE
,
            // default
            [self::ONLY_DIRECT_ASSIGN => \true]
        ), new ConfiguredCodeSample(<<<'CODE_SAMPLE'
function () {
    $a = 'Hello, ';
    $a .= 'World!';

    return $a;
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function () {
    $a = 'Hello, ';

    return $a . 'World!';
};
CODE_SAMPLE
, [self::ONLY_DIRECT_ASSIGN => \false])]);
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
            if ($this->isReturnWithVarAnnotation($stmt)) {
                return null;
            }
            /** @var Expression<Assign|AssignOp> $previousStmt */
            $assign = $previousStmt->expr;
            return $this->processSimplifyUselessVariable($node, $stmt, $assign, $key);
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\Assign|\PhpParser\Node\Expr\AssignOp $assign
     */
    private function processSimplifyUselessVariable(StmtsAwareInterface $stmtsAware, Return_ $return, $assign, int $key) : ?StmtsAwareInterface
    {
        if (!$assign instanceof Assign) {
            $binaryClass = $this->assignAndBinaryMap->getAlternative($assign);
            if ($binaryClass === null) {
                return null;
            }
            $return->expr = new $binaryClass($assign->var, $assign->expr);
        } else {
            $return->expr = $assign->expr;
        }
        unset($stmtsAware->stmts[$key - 1]);
        return $stmtsAware;
    }
    private function shouldSkipStmt(Return_ $return, Stmt $previousStmt) : bool
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
        if (!$previousNode instanceof AssignOp && !$previousNode instanceof Assign) {
            return \true;
        }
        if ($this->onlyDirectAssign && $previousNode instanceof AssignOp) {
            return \true;
        }
        if ($previousNode instanceof AssignOp && $previousNode->expr instanceof Ternary) {
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
    private function hasSomeComment(Stmt $stmt) : bool
    {
        if ($stmt->getComments() !== []) {
            return \true;
        }
        return $stmt->getDocComment() instanceof Doc;
    }
    private function isReturnWithVarAnnotation(Return_ $return) : bool
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($return);
        return !$phpDocInfo->getVarType() instanceof MixedType;
    }
}
