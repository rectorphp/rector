<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Contract\TypeInferer;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\Type;
use Rector\TypeDeclaration\Contract\PriorityAwareInterface;
interface ReturnTypeInfererInterface extends \Rector\TypeDeclaration\Contract\PriorityAwareInterface
{
    public function inferFunctionLike(\PhpParser\Node\FunctionLike $functionLike) : \PHPStan\Type\Type;
}
