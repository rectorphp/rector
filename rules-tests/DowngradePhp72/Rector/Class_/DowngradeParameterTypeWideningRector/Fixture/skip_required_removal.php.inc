<?php

namespace Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

use PhpParser\Node\Param;
use PHPStan\Type\Type;
use Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;

final class SkipRequiredRemoval implements ParamTypeInfererInterface
{
    public function inferParam(Param $param): Type
    {
    }
}
