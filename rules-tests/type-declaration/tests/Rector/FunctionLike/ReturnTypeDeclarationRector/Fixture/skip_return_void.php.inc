<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class SkipReturnVoid
{
    /**
     * @return string|void
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route2("test");
        }
    }
}

/**
 * @param  string  $name
 */
function route($name, $parameters = [], $absolute = true): string
{
    return $name;
}
