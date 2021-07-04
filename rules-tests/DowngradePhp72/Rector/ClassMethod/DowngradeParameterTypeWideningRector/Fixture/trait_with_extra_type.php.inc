<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

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
----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\ClassMethod\DowngradeParameterTypeWideningRector\Fixture;

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

    /**
     * @param mixed[] $elements
     */
    protected function createStream($elements)
    {
        // ...
    }
}

?>
