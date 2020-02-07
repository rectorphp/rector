<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\TypeInferer;

use PhpParser\Node\Param;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;

final class ParamTypeInferer
{
    /**
     * @var ParamTypeInfererInterface[]
     */
    private $paramTypeInferers = [];

    /**
     * @param ParamTypeInfererInterface[] $paramTypeInferers
     */
    public function __construct(array $paramTypeInferers)
    {
        $this->paramTypeInferers = $paramTypeInferers;
    }

    public function inferParam(Param $param): Type
    {
        foreach ($this->paramTypeInferers as $paramTypeInferers) {
            $type = $paramTypeInferers->inferParam($param);
            if (! $type instanceof MixedType) {
                return $type;
            }
        }

        return new MixedType();
    }
}
