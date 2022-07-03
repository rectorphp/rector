<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\NodeManipulator\TokenManipulator;
use Rector\PostRector\Collector\NodesToAddCollector;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/token_as_object
 *
 * @see \Rector\Tests\Php80\Rector\FuncCall\TokenGetAllToObjectRector\TokenGetAllToObjectRectorTest
 */
final class TokenGetAllToObjectRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeManipulator\TokenManipulator
     */
    private $tokenManipulator;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\NodesToAddCollector
     */
    private $nodesToAddCollector;
    public function __construct(TokenManipulator $tokenManipulator, NodesToAddCollector $nodesToAddCollector)
    {
        $this->tokenManipulator = $tokenManipulator;
        $this->nodesToAddCollector = $nodesToAddCollector;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::PHP_TOKEN;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert `token_get_all` to `PhpToken::tokenize`', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $tokens = token_get_all($code);
        foreach ($tokens as $token) {
            if (is_array($token)) {
               $name = token_name($token[0]);
               $text = $token[1];
            } else {
               $name = null;
               $text = $token;
            }
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $tokens = \PhpToken::tokenize($code);
        foreach ($tokens as $phpToken) {
           $name = $phpToken->getTokenName();
           $text = $phpToken->text;
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeNameResolver->isName($node, 'token_get_all')) {
            return null;
        }
        $this->refactorTokensVariable($node);
        return $this->nodeFactory->createStaticCall('PhpToken', 'tokenize', $node->args);
    }
    private function refactorTokensVariable(FuncCall $funcCall) : void
    {
        $assign = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if (!$assign instanceof Assign) {
            return;
        }
        /** @var ClassMethod|Function_|null $classMethodOrFunction */
        $classMethodOrFunction = $this->betterNodeFinder->findParentByTypes($funcCall, [ClassMethod::class, Function_::class]);
        if ($classMethodOrFunction === null) {
            return;
        }
        // dummy approach, improve when needed
        $this->replaceGetNameOrGetValue($classMethodOrFunction, $assign->var);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function replaceGetNameOrGetValue($functionLike, Expr $assignedExpr) : void
    {
        $tokensForeaches = $this->findForeachesOverTokenVariable($functionLike, $assignedExpr);
        foreach ($tokensForeaches as $tokenForeach) {
            $this->refactorTokenInForeach($tokenForeach);
        }
    }
    /**
     * @return Foreach_[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    private function findForeachesOverTokenVariable($functionLike, Expr $assignedExpr) : array
    {
        return $this->betterNodeFinder->find((array) $functionLike->stmts, function (Node $node) use($assignedExpr) : bool {
            if (!$node instanceof Foreach_) {
                return \false;
            }
            return $this->nodeComparator->areNodesEqual($node->expr, $assignedExpr);
        });
    }
    private function refactorTokenInForeach(Foreach_ $tokensForeach) : void
    {
        $singleToken = $tokensForeach->valueVar;
        if (!$singleToken instanceof Variable) {
            return;
        }
        $this->traverseNodesWithCallable($tokensForeach, function (Node $node) use($singleToken) {
            $this->tokenManipulator->refactorArrayToken([$node], $singleToken);
            $this->tokenManipulator->refactorNonArrayToken([$node], $singleToken);
            $this->tokenManipulator->refactorTokenIsKind([$node], $singleToken);
            $this->tokenManipulator->removeIsArray([$node], $singleToken);
            // drop if "If_" node not needed
            if ($node instanceof If_ && $node->else !== null) {
                if (!$this->nodeComparator->areNodesEqual($node->stmts, $node->else->stmts)) {
                    return null;
                }
                $this->unwrapStmts($node->stmts, $node);
                $this->removeNode($node);
            }
        });
    }
    /**
     * @param Stmt[] $stmts
     */
    private function unwrapStmts(array $stmts, If_ $if) : void
    {
        // move /* */ doc block from if to first element to keep it
        $currentPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($if);
        foreach ($stmts as $key => $stmt) {
            if ($key === 0) {
                $stmt->setAttribute(AttributeKey::PHP_DOC_INFO, $currentPhpDocInfo);
                // move // comments
                $stmt->setAttribute(AttributeKey::COMMENTS, $if->getComments());
            }
            $this->nodesToAddCollector->addNodeAfterNode($stmt, $if);
        }
    }
}
