<?php
trait StreamDecoratorTrait
{
    protected function createStream()
    {
        // ...
    }
}

namespace Rector\DowngradePhp72\Tests\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

class MultipartStream
{
    use StreamDecoratorTrait;

    protected function createStream(array $elements)
    {
        // ...
    }
}
?>
