<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\Source\SomeTypedService;

final class UseDependency
{
    public function __construct(
        private SomeTypedService $someTypedService
    ) {
    }

    public function go($value)
    {
        $this->someTypedService->run($value);
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector\Source\SomeTypedService;

final class UseDependency
{
    public function __construct(
        private SomeTypedService $someTypedService
    ) {
    }

    public function go(string $value)
    {
        $this->someTypedService->run($value);
    }
}

?>
