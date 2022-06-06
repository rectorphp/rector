<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;

use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeTypeResolver;
use RectorPrefix20220606\Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;
use RectorPrefix20220606\Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower;
final class SplFixedArrayParamTypeInferer implements ParamTypeInfererInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower
     */
    private $splArrayFixedTypeNarrower;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(SplArrayFixedTypeNarrower $splArrayFixedTypeNarrower, NodeTypeResolver $nodeTypeResolver)
    {
        $this->splArrayFixedTypeNarrower = $splArrayFixedTypeNarrower;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function inferParam(Param $param) : Type
    {
        if ($param->type === null) {
            return new MixedType();
        }
        $paramType = $this->nodeTypeResolver->getType($param->type);
        return $this->splArrayFixedTypeNarrower->narrow($paramType);
    }
}
