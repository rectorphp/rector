<?php

namespace Rector\Laravel\Tests\Rector\New_\MakeTaggedPassedToParameterIterableTypeRector\Fixture;

class AnotherClass
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    private $app;

    public function create()
    {
        $tagged = $this->app->tagged('some_tagged');
        return new SomeClass($tagged);
    }
}

class SomeClass
{
    public function __construct(array $items)
    {
    }
}

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\New_\MakeTaggedPassedToParameterIterableTypeRector\Fixture;

class AnotherClass
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    private $app;

    public function create()
    {
        $tagged = $this->app->tagged('some_tagged');
        return new SomeClass($tagged);
    }
}

class SomeClass
{
    public function __construct(iterable $items)
    {
    }
}

?>
