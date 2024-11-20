<?php

declare (strict_types=1);
namespace Rector\NodeManipulator;

use PhpParser\Node\FunctionLike;
use Rector\NodeNameResolver\NodeNameResolver;
final class FunctionLikeManipulator
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return string[]
     */
    public function resolveParamNames(FunctionLike $functionLike) : array
    {
        $paramNames = [];
        foreach ($functionLike->getParams() as $param) {
            $paramNames[] = $this->nodeNameResolver->getName($param);
        }
        return $paramNames;
    }
}
