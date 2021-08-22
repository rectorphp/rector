<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\Param;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
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
     * @param ParamTypeInfererInterface[] $paramTypeInferers
     */
    public function __construct(\Rector\TypeDeclaration\TypeAnalyzer\GenericClassStringTypeNormalizer $genericClassStringTypeNormalizer, array $paramTypeInferers)
    {
        $this->genericClassStringTypeNormalizer = $genericClassStringTypeNormalizer;
        $this->paramTypeInferers = $paramTypeInferers;
    }
    public function inferParam(\PhpParser\Node\Param $param) : \PHPStan\Type\Type
    {
        foreach ($this->paramTypeInferers as $paramTypeInferer) {
            $paramType = $paramTypeInferer->inferParam($param);
            if ($paramType instanceof \PHPStan\Type\MixedType) {
                continue;
            }
            return $this->genericClassStringTypeNormalizer->normalize($paramType);
        }
        return new \PHPStan\Type\MixedType();
    }
}
