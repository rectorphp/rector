<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureInlinePublic;

final class SimpleArray
{
    /**
     * @var array
     */
    private $foo;

    /**
     * @var array
     */
    protected $bar;

    /**
     * @var array
     */
    public $baz;
}

?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\FixtureInlinePublic;

final class SimpleArray
{
    private array $foo;

    protected array $bar;

    public array $baz;
}

?>
