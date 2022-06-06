<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\Contract\TypeInferer;

use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PHPStan\Type\Type;
interface ParamTypeInfererInterface
{
    public function inferParam(Param $param) : Type;
}
