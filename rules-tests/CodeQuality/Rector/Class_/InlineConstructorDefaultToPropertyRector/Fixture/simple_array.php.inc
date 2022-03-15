<?php

namespace Rector\Tests\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector\Fixture;

final class SimpleArray
{
    private $items;

    public function __construct()
    {
        $this->items = ['John', true, 252 => [null]];
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector\Fixture;

final class SimpleArray
{
    private $items = ['John', true, 252 => [null]];

    public function __construct()
    {
    }
}

?>
