<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\NodeTraverser;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;

final class ReturnedNodesReturnTypeInferer extends AbstractTypeInferer implements ReturnTypeInfererInterface
{
    /**
     * @param ClassMethod|Closure|Function_ $functionLike
     */
    public function inferFunctionLike(FunctionLike $functionLike): Type
    {
        /** @var Class_|Trait_|Interface_|null $classLike */
        $classLike = $functionLike->getAttribute(AttributeKey::CLASS_NODE);
        if ($functionLike instanceof ClassMethod) {
            if ($classLike instanceof Interface_) {
                return new MixedType();
            }
        }

        $localReturnNodes = $this->collectReturns($functionLike);
        if ($localReturnNodes === []) {
            // void type
            if ($functionLike instanceof ClassMethod && ! $functionLike->isAbstract()) {
                return new VoidType();
            }

            return new MixedType();
        }

        $types = [];
        foreach ($localReturnNodes as $localReturnNode) {
            if ($localReturnNode->expr === null) {
                continue;
            }

            $staticType = $this->nodeTypeResolver->resolveNodeToPHPStanType($localReturnNode->expr);
            $types[] = $staticType;
        }

        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }

    public function getPriority(): int
    {
        return 1000;
    }

    /**
     * @return Return_[]
     */
    private function collectReturns(FunctionLike $functionLike): array
    {
        $returns = [];

        $this->callableNodeTraverser->traverseNodesWithCallable((array) $functionLike->getStmts(), function (
            Node $node
        ) use (&$returns): ?int {
            // skip Return_ nodes in nested functions
            if ($node instanceof FunctionLike) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }

            if (! $node instanceof Return_) {
                return null;
            }

            if ($node->expr === null) {
                return null;
            }

            $returns[] = $node;

            return null;
        });

        return $returns;
    }
}
