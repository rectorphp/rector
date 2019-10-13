<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use Iterator;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Rector\Php\PhpVersionProvider;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\TypeDeclaration\Contract\TypeInferer\ReturnTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\AbstractTypeInferer;

final class YieldNodesReturnTypeInferer extends AbstractTypeInferer implements ReturnTypeInfererInterface
{
    /**
     * @var BetterNodeFinder
     */
    private $betterNodeFinder;

    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    public function __construct(BetterNodeFinder $betterNodeFinder, PhpVersionProvider $phpVersionProvider)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->phpVersionProvider = $phpVersionProvider;
    }

    /**
     * @param ClassMethod|Function_|Closure $functionLike
     */
    public function inferFunctionLike(FunctionLike $functionLike): Type
    {
        /** @var Yield_[] $yieldNodes */
        $yieldNodes = $this->betterNodeFinder->findInstanceOf((array) $functionLike->stmts, Yield_::class);

        $types = [];
        if (count($yieldNodes)) {
            foreach ($yieldNodes as $yieldNode) {
                if ($yieldNode->value === null) {
                    continue;
                }

                $yieldValueStaticType = $this->nodeTypeResolver->resolveNodeToPHPStanType($yieldNode->value);
                $types[] = new ArrayType(new MixedType(), $yieldValueStaticType);
            }

            if ($this->phpVersionProvider->isAtLeast('7.1')) {
                // @see https://www.php.net/manual/en/language.types.iterable.php
                $types[] = new IterableType(new MixedType(), new MixedType());
            } else {
                $types[] = new ObjectType(Iterator::class);
            }
        }

        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }

    public function getPriority(): int
    {
        return 1200;
    }
}
