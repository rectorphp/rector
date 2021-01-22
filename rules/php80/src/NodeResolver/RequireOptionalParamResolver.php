<?php

declare(strict_types=1);

namespace Rector\Php80\NodeResolver;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;

final class RequireOptionalParamResolver
{
    /**
     * @param ClassMethod $functionLike
     * @return Param[]
     */
    public function resolve(FunctionLike $functionLike): array
    {
        $optionalParams = [];
        $requireParams = [];
        foreach ($functionLike->getParams() as $param) {
            if ($param->default === null) {
                $requireParams[] = $param;
            } else {
                $optionalParams[] = $param;
            }
        }

        return array_merge($requireParams, $optionalParams);
    }
}
