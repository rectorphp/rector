<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Contract\TypeInferer;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\Type;
interface ReturnTypeInfererInterface extends \Rector\TypeDeclaration\Contract\TypeInferer\PriorityAwareTypeInfererInterface
{
    /**
     * @param \PhpParser\Node\FunctionLike $functionLike
     */
    public function inferFunctionLike($functionLike) : \PHPStan\Type\Type;
}
