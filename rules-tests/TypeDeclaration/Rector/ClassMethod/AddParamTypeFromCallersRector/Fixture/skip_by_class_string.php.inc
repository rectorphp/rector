<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromCallersRector\Fixture;

use Rector\Core\Contract\Rector\PhpRectorInterface;

final class SkipByClassString
{
    public function run()
    {
        $this->provideByType(PhpRectorInterface::class);
    }

    public function provideByType(string $type)
    {
    }
}
