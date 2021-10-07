<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node;
use PhpParser\Node\Param;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;
use Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
final class ParamTypeInferer
{
    /**
     * @var \Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer
     */
    private $genericClassStringTypeNormalizer;
    /**
     * @var \Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface[]
     */
    private $paramTypeInferers;
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @param ParamTypeInfererInterface[] $paramTypeInferers
     */
    public function __construct(\Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer, array $paramTypeInferers, \Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->genericClassStringTypeNormalizer = $genericClassStringTypeNormalizer;
        $this->paramTypeInferers = $paramTypeInferers;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function inferParam(\PhpParser\Node\Param $param) : \PHPStan\Type\Type
    {
        foreach ($this->paramTypeInferers as $paramTypeInferer) {
            $paramType = $paramTypeInferer->inferParam($param);
            if ($paramType instanceof \PHPStan\Type\MixedType) {
                continue;
            }
            $inferedType = $this->genericClassStringTypeNormalizer->normalize($paramType);
            if ($param->default instanceof \PhpParser\Node) {
                $paramDefaultType = $this->nodeTypeResolver->getType($param->default);
                if (!$paramDefaultType instanceof $inferedType) {
                    return new \PHPStan\Type\MixedType();
                }
            }
            return $inferedType;
        }
        return new \PHPStan\Type\MixedType();
    }
}
