<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeTraverser;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
        /** @var Class_|Trait_|Interface_|null $classLike */
        $classLike = $functionLike->getAttribute(AttributeKey::CLASS_NODE);
        if ($functionLike instanceof ClassMethod) {
            if ($classLike instanceof Interface_) {
                return [];
            }
        }

        $localReturnNodes = $this->collectReturns($functionLike);
        if ($localReturnNodes === []) {
            // void type
            if ($functionLike instanceof ClassMethod && ! $functionLike->isAbstract()) {
                return ['void'];
            }

            return [];
        }

        $types = [];
        foreach ($localReturnNodes as $localReturnNode) {
            $types = array_merge($types, $this->nodeTypeResolver->resolveSingleTypeToStrings($localReturnNode->expr));
        }

        // @todo add priority, because this gets last :)
        return $types;
    }

    public function getPriority(): int
    {
        return 1000;
    }

    /**
     * @param ClassMethod|Closure|Function_ $functionLike
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
