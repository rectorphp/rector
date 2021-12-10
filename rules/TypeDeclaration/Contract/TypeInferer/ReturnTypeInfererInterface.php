<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Contract\TypeInferer;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\Type;
interface ReturnTypeInfererInterface extends \Rector\TypeDeclaration\Contract\TypeInferer\PriorityAwareTypeInfererInterface
{
    public function inferFunctionLike(\PhpParser\Node\FunctionLike $functionLike) : \PHPStan\Type\Type;
}
