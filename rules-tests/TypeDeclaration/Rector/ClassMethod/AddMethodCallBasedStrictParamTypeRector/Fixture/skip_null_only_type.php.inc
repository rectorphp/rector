<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

final class SkipNullOnlyType
{
    public function run(): void
    {
        $this->doSomething(null);
    }

    private function doSomething($a): void
    {
    }
}
?>
