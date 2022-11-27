<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node;
use PhpParser\Node\Param;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
use Rector\TypeDeclaration\TypeInferer\ParamTypeInferer\FunctionLikeDocParamTypeInferer;
final class ParamTypeInferer
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer
     */
    private $genericClassStringTypeNormalizer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\ParamTypeInferer\FunctionLikeDocParamTypeInferer
     */
    private $functionLikeDocParamTypeInferer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer, FunctionLikeDocParamTypeInferer $functionLikeDocParamTypeInferer, NodeTypeResolver $nodeTypeResolver)
    {
        $this->genericClassStringTypeNormalizer = $genericClassStringTypeNormalizer;
        $this->functionLikeDocParamTypeInferer = $functionLikeDocParamTypeInferer;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function inferParam(Param $param) : Type
    {
        $paramType = $this->functionLikeDocParamTypeInferer->inferParam($param);
        if ($paramType instanceof MixedType) {
            return new MixedType();
        }
        $inferedType = $this->genericClassStringTypeNormalizer->normalize($paramType);
        if ($param->default instanceof Node) {
            $paramDefaultType = $this->nodeTypeResolver->getType($param->default);
            if (!$paramDefaultType instanceof $inferedType) {
                return new MixedType();
            }
        }
        return $inferedType;
    }
}
