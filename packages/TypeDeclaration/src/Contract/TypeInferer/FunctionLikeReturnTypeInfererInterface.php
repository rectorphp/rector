<?php declare(strict_types=1);

namespace Rector\TypeDeclaration\Contract\TypeInferer;

use PhpParser\Node\FunctionLike;

interface FunctionLikeReturnTypeInfererInterface
{
    /**
     * @return string[]
     */
    public function inferFunctionLike(FunctionLike $functionLike): array;
}
