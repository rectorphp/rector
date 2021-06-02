<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\If_;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php80\NodeManipulator\TokenManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/token_as_object
 *
 * @see \Rector\Tests\Php80\Rector\FuncCall\TokenGetAllToObjectRector\TokenGetAllToObjectRectorTest
 */
final class TokenGetAllToObjectRector extends AbstractRector
{
    public function __construct(
        private TokenManipulator $tokenManipulator
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Convert `token_get_all` to `PhpToken::getAll`',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
,
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $tokens = \PhpToken::getAll($code);
        foreach ($tokens as $phpToken) {
           $name = $phpToken->getTokenName();
           $text = $phpToken->text;
        }
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->nodeNameResolver->isName($node, 'token_get_all')) {
            return null;
        }

        $this->refactorTokensVariable($node);

        return $this->nodeFactory->createStaticCall('PhpToken', 'getAll', $node->args);
    }

    private function refactorTokensVariable(FuncCall $funcCall): void
    {
        $assign = $funcCall->getAttribute(AttributeKey::PARENT_NODE);
        if (! $assign instanceof Assign) {
            return;
        }

        /** @var ClassMethod|Function_|null $classMethodOrFunction */
        $classMethodOrFunction = $this->betterNodeFinder->findParentTypes(
            $funcCall,
            [ClassMethod::class, Function_::class]
        );
        if ($classMethodOrFunction === null) {
            return;
        }

        // dummy approach, improve when needed
        $this->replaceGetNameOrGetValue($classMethodOrFunction, $assign->var);
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     */
    private function replaceGetNameOrGetValue(FunctionLike $functionLike, Expr $assignedExpr): void
    {
        $tokensForeaches = $this->findForeachesOverTokenVariable($functionLike, $assignedExpr);
        foreach ($tokensForeaches as $tokenForeach) {
            $this->refactorTokenInForeach($tokenForeach);
        }
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     * @return Foreach_[]
     */
    private function findForeachesOverTokenVariable(FunctionLike $functionLike, Expr $assignedExpr): array
    {
        return $this->betterNodeFinder->find((array) $functionLike->stmts, function (Node $node) use (
            $assignedExpr
        ): bool {
            if (! $node instanceof Foreach_) {
                return false;
            }

            return $this->nodeComparator->areNodesEqual($node->expr, $assignedExpr);
        });
    }

    private function refactorTokenInForeach(Foreach_ $tokensForeach): void
    {
        $singleToken = $tokensForeach->valueVar;
        if (! $singleToken instanceof Variable) {
            return;
        }

        $this->traverseNodesWithCallable($tokensForeach, function (Node $node) use ($singleToken) {
            $this->tokenManipulator->refactorArrayToken([$node], $singleToken);
            $this->tokenManipulator->refactorNonArrayToken([$node], $singleToken);
            $this->tokenManipulator->refactorTokenIsKind([$node], $singleToken);

            $this->tokenManipulator->removeIsArray([$node], $singleToken);

            // drop if "If_" node not needed
            if ($node instanceof If_ && $node->else !== null) {
                if (! $this->nodeComparator->areNodesEqual($node->stmts, $node->else->stmts)) {
                    return null;
                }

                $this->unwrapStmts($node->stmts, $node);
                $this->removeNode($node);
            }
        });
    }
}
