<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;

use PhpParser\Node\Param;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower;

final class SplFixedArrayParamTypeInferer implements ParamTypeInfererInterface
{
    public function __construct(
        private SplArrayFixedTypeNarrower $splArrayFixedTypeNarrower,
        private NodeTypeResolver $nodeTypeResolver
    ) {
    }

    public function inferParam(Param $param): Type
    {
        if ($param->type === null) {
            return new MixedType();
        }

        $paramType = $this->nodeTypeResolver->resolve($param->type);
        return $this->splArrayFixedTypeNarrower->narrow($paramType);
    }
}
