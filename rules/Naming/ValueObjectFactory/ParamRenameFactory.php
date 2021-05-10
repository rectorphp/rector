<?php

declare(strict_types=1);

namespace Rector\Naming\ValueObjectFactory;

use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Naming\ValueObject\ParamRename;
use Rector\NodeNameResolver\NodeNameResolver;

final class ParamRenameFactory
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private BetterNodeFinder $betterNodeFinder
    ) {
    }

    public function createFromResolvedExpectedName(Param $param, string $expectedName): ?ParamRename
    {
        /** @var ClassMethod|Function_|Closure|ArrowFunction|null $functionLike */
        $functionLike = $this->betterNodeFinder->findParentType($param, FunctionLike::class);
        if ($functionLike === null) {
            throw new ShouldNotHappenException("There shouldn't be a param outside of FunctionLike");
        }

        if ($functionLike instanceof ArrowFunction) {
            return null;
        }

        $currentName = $this->nodeNameResolver->getName($param->var);
        if ($currentName === null) {
            return null;
        }
        return new ParamRename($currentName, $expectedName, $param, $param->var, $functionLike);
    }
}
