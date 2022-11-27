<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Contract\TypeInferer;

use PhpParser\Node\FunctionLike;
use PHPStan\Type\Type;
use Rector\TypeDeclaration\Contract\PriorityAwareInterface;
/**
 * @deprecated These inferers are complex and work with non-reliable docblocks.
 * Instead split them to specific rules that work with strict types.
 */
interface ReturnTypeInfererInterface extends PriorityAwareInterface
{
    public function inferFunctionLike(FunctionLike $functionLike) : Type;
}
