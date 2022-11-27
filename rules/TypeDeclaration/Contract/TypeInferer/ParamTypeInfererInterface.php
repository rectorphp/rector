<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Contract\TypeInferer;

use PhpParser\Node\Param;
use PHPStan\Type\Type;
/**
 * @deprecated This interface is not used anymore. Use the exact Rector rules instead.
 */
interface ParamTypeInfererInterface
{
    public function inferParam(Param $param) : Type;
}
