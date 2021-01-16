<?php
declare(strict_types=1);

namespace Rector\Nette\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Symplify\Astral\NodeTraverser\SimpleCallableNodeTraverser;

final class ParentGetterStmtsToExternalStmtsFactory
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        NodeTypeResolver $nodeTypeResolver,
        SimpleCallableNodeTraverser $simpleCallableNodeTraverser,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @param Node[] $getUserStmts
     * @return Node[]
     */
    public function create(array $getUserStmts): array
    {
        $userExpression = null;

        foreach ($getUserStmts as $key => $getUserStmt) {
            if (! $getUserStmt instanceof Expression) {
                continue;
            }

            $getUserStmt = $getUserStmt->expr;
            if (! $getUserStmt instanceof Assign) {
                continue;
            }

            if (! $getUserStmt->expr instanceof StaticCall) {
                continue;
            }

            if (! $this->nodeTypeResolver->isObjectType($getUserStmt->expr, 'Nette\Security\User')) {
                continue;
            }

            $userExpression = $getUserStmt->var;
            unset($getUserStmts[$key]);
        }

        $getUserStmts = $this->removeReturn($getUserStmts);

        // nothing we can do
        if ($userExpression === null) {
            return [];
        }

        // stmts without assign
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($getUserStmts, function (Node $node) use (
            $userExpression
        ): ?MethodCall {
            if (! $this->betterStandardPrinter->areNodesEqual($node, $userExpression)) {
                return null;
            }

            return new MethodCall(new Variable('this'), 'getUser');
        });

        return $getUserStmts;
    }

    /**
     * @param Node[] $stmts
     * @return Node[]
     */
    private function removeReturn(array $stmts): array
    {
        foreach ($stmts as $key => $stmt) {
            if (! $stmt instanceof Return_) {
                continue;
            }

            unset($stmts[$key]);
        }

        return $stmts;
    }
}
