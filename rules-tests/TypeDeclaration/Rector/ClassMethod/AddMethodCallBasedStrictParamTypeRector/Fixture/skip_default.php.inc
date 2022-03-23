<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

final class SkipDefault
{
    public function runFirst()
    {
        $this->process('first');
    }

    public function runEmpty()
    {
        $this->process();
    }

    private function process($data = false)
    {
    }
}
