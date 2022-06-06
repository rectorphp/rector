<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\Contract\TypeInferer;

use RectorPrefix20220606\PhpParser\Node\FunctionLike;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\Rector\TypeDeclaration\Contract\PriorityAwareInterface;
interface ReturnTypeInfererInterface extends PriorityAwareInterface
{
    public function inferFunctionLike(FunctionLike $functionLike) : Type;
}
