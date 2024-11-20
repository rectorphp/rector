<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\NodeAnalyzer;

use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use Rector\NodeNameResolver\NodeNameResolver;
final class ParamAnalyzer
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function getParamByName(string $desiredParamName, FunctionLike $functionLike) : ?Param
    {
        foreach ($functionLike->getParams() as $param) {
            $paramName = $this->nodeNameResolver->getName($param);
            if ('$' . $paramName !== $desiredParamName) {
                continue;
            }
            return $param;
        }
        return null;
    }
}
