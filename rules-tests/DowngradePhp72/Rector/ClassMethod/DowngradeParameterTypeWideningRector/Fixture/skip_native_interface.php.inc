<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

class SkipNativeInterface implements \SplObserver
{
    public function update(\SplSubject $subject): void
    {
    }
}
