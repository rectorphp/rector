<?php

declare(strict_types=1);

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
     * @param ParamTypeInfererInterface[] $paramTypeInferers
     */
    public function __construct(
        private GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer,
        private array $paramTypeInferers,
        private NodeTypeResolver $nodeTypeResolver
    ) {
    }

    public function inferParam(Param $param): Type
    {
        foreach ($this->paramTypeInferers as $paramTypeInferer) {
            $paramType = $paramTypeInferer->inferParam($param);
            if ($paramType instanceof MixedType) {
                continue;
            }

            $inferedType = $this->genericClassStringTypeNormalizer->normalize($paramType);
            if ($param->default instanceof Node) {
                $paramDefaultType = $this->nodeTypeResolver->resolve($param->default);
                if (! $paramDefaultType instanceof $inferedType) {
                    return new MixedType();
                }
            }

            return $inferedType;
        }

        return new MixedType();
    }
}
