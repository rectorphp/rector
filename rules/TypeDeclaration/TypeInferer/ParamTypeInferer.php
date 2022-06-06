<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\TypeInferer;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix20220606\Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;
use RectorPrefix20220606\Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer;
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
