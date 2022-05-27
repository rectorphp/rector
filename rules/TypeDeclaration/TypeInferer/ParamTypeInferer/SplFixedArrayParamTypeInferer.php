<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer\ParamTypeInferer;

use PhpParser\Node\Param;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;
use Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower;
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
