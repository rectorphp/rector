<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;

final class ReturnedNodesReturnTypeInferer extends AbstractTypeInferer implements ReturnTypeInfererInterface
{
    /**
     * @param ClassMethod|Closure|Function_ $functionLike
     * @return string[]
     */
    public function inferFunctionLike(FunctionLike $functionLike): array
    {
        $localReturnNodes = $this->collectReturns($functionLike);
        if ($localReturnNodes === []) {
            return [];
        }

        $types = [];
        foreach ($localReturnNodes as $localReturnNode) {
            $types = array_merge($types, $this->nodeTypeResolver->resolveSingleTypeToStrings($localReturnNode->expr));
        }

        // @todo add priority, because this gets last :)
        return $types;
    }

    /**
     * @param ClassMethod|Function_ $functionLike
     * @return Return_[]
     */
    private function collectReturns(FunctionLike $functionLike): array
    {
        $returns = [];

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $functionLike->stmts, function (Node $node) use (
            &$returns
        ): ?int {
            if ($node instanceof Function_ || $node instanceof Closure || $node instanceof ArrowFunction) {
                // skip Return_ nodes in nested functions
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }

            if (! $node instanceof Return_) {
                return null;
            }

            // skip void returns
            if ($node->expr === null) {
                return null;
            }

            $returns[] = $node;

            return null;
        });

        return $returns;
    }
}
