<?php

namespace Rector\DowngradePhp72\Tests\Rector\Class_\DowngradeParameterTypeWideningRector\Fixture;

trait StreamDecoratorTrait
{
    protected function createStream()
    {
        // ...
    }
}

class MultipartStream
{
    use StreamDecoratorTrait;

    protected function createStream(array $elements)
    {
        // ...
    }
}
?>
