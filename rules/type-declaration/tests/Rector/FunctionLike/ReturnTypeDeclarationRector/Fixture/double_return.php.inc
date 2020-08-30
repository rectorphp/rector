<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class DoubleReturn
{
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return $this->getName();
        }

        return '';
    }

    private function getName(): string
    {
        return 'name';
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class DoubleReturn
{
    protected function redirectTo($request): string
    {
        if (! $request->expectsJson()) {
            return $this->getName();
        }

        return '';
    }

    private function getName(): string
    {
        return 'name';
    }
}

?>
