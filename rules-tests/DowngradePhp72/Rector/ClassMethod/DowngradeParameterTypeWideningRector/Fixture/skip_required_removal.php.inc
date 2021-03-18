<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

use PhpParser\Node\Param;
use PHPStan\Type\Type;
use Rector\TypeDeclaration\Contract\TypeInferer\ParamTypeInfererInterface;

final class SkipRequiredRemoval implements ParamTypeInfererInterface
{
    public function autowireSomething(\PHPStan\Type\Type $input)
    {
    }

    public function inferParam(Param $param): Type
    {
    }
}
