<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Closure;

final class skipReturnClosureToCallable
{
    public function run(): Closure
    {
        return fn ($query) => $query->where('id', 1);
    }
}
