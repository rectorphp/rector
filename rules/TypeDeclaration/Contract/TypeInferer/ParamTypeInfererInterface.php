<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Contract\TypeInferer;

use PhpParser\Node\Param;
use PHPStan\Type\Type;
interface ParamTypeInfererInterface
{
    /**
     * @param \RectorPrefix20210822\PhpParser\Node\Param $param
     */
    public function inferParam($param) : \PHPStan\Type\Type;
}
