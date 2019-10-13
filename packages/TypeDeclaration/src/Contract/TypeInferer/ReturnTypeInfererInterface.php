<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Contract\TypeInferer;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\Type;

interface ReturnTypeInfererInterface extends PriorityAwareTypeInfererInterface
{
    public function inferFunctionLike(FunctionLike $functionLike): Type;
}
