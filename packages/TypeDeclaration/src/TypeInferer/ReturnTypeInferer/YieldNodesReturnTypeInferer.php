<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use Iterator;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
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
     * @return string[]
     */
    public function inferFunctionLike(FunctionLike $functionLike): array
    {
        /** @var Yield_[] $yieldNodes */
        $yieldNodes = $this->betterNodeFinder->findInstanceOf((array) $functionLike->stmts, Yield_::class);

        $types = [];
        if (count($yieldNodes)) {
            foreach ($yieldNodes as $yieldNode) {
                if ($yieldNode->value === null) {
                    continue;
                }

                $resolvedTypes = $this->nodeTypeResolver->resolveSingleTypeToStrings($yieldNode->value);
                foreach ($resolvedTypes as $resolvedType) {
                    $types[] = $resolvedType . '[]';
                }
            }

            if ($this->phpVersionProvider->isAtLeast('7.1')) {
                // @see https://www.php.net/manual/en/language.types.iterable.php
                $types[] = 'iterable';
            } else {
                $types[] = Iterator::class;
            }
        }

        return array_unique($types);
    }

    public function getPriority(): int
    {
        return 1200;
    }
}
