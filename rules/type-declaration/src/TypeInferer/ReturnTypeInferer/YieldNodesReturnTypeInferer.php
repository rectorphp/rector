<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use Iterator;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Expr\YieldFrom;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeTraverser;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;

final class YieldNodesReturnTypeInferer extends AbstractTypeInferer implements ReturnTypeInfererInterface
{
    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }

    /**
     * @param ClassMethod|Function_|Closure $functionLike
     */
    public function inferFunctionLike(FunctionLike $functionLike): Type
    {
        $yieldNodes = $this->findCurrentScopeYieldNodes($functionLike);

        if ($yieldNodes === []) {
            return new MixedType();
        }

        $types = [];
        foreach ($yieldNodes as $yieldNode) {
            $value = $this->resolveYieldValue($yieldNode);
            if (! $value instanceof Expr) {
                continue;
            }

            $yieldValueStaticType = $this->nodeTypeResolver->getStaticType($value);
            $types[] = new ArrayType(new MixedType(), $yieldValueStaticType);
        }

        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::ITERABLE_TYPE)) {
            // @see https://www.php.net/manual/en/language.types.iterable.php
            $types[] = new IterableType(new MixedType(), new MixedType());
        } else {
            $types[] = new ObjectType(Iterator::class);
        }

        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }

    public function getPriority(): int
    {
        return 1200;
    }

    /**
     * @return Yield_[]|YieldFrom[]
     */
    private function findCurrentScopeYieldNodes(FunctionLike $functionLike): array
    {
        $yieldNodes = [];

        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $functionLike->getStmts(), function (
            Node $node
        ) use (&$yieldNodes): ?int {
            // skip nested scope
            if ($node instanceof FunctionLike) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }

            if (! $node instanceof Yield_ && ! $node instanceof YieldFrom) {
                return null;
            }

            $yieldNodes[] = $node;
            return null;
        });

        return $yieldNodes;
    }

    /**
     * @param Yield_|YieldFrom $yieldExpr
     */
    private function resolveYieldValue(Expr $yieldExpr): ?Expr
    {
        if ($yieldExpr instanceof Yield_) {
            return $yieldExpr->value;
        }

        return $yieldExpr->expr;
    }
}
