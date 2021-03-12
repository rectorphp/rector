<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector\Fixture;

final class KeepAnonymous
{
    public function createContainerFromKernelClass(string $kernelClass)
    {
        $kernel = $this->createKernelFromKernelClass($kernelClass);
        $anonymous = new class {};
    }

    private function createKernelFromKernelClass($kernelClass)
    {
        return $kernelClass;
    }
}
