<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Contract\TypeInferer;

use PhpParser\Node\Param;
use PHPStan\Type\Type;
interface ParamTypeInfererInterface
{
    public function inferParam(Param $param) : Type;
}
