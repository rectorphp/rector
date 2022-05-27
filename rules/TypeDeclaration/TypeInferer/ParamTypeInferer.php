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
     * @readonly
     * @var \Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer
     */
    private $genericClassStringTypeNormalizer;
    /**
     * @var ParamTypeInfererInterface[]
     * @readonly
     */
    private $paramTypeInferers;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @param ParamTypeInfererInterface[] $paramTypeInferers
     */
    public function __construct(GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer, array $paramTypeInferers, NodeTypeResolver $nodeTypeResolver)
    {
        $this->genericClassStringTypeNormalizer = $genericClassStringTypeNormalizer;
        $this->paramTypeInferers = $paramTypeInferers;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function inferParam(Param $param) : Type
    {
        foreach ($this->paramTypeInferers as $paramTypeInferer) {
            $paramType = $paramTypeInferer->inferParam($param);
            if ($paramType instanceof MixedType) {
                continue;
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
        return new MixedType();
    }
}
