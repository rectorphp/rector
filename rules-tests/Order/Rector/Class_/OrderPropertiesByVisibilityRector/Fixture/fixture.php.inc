<?php

namespace Rector\Tests\Order\Rector\Class_\OrderPropertiesByVisibilityRector\Fixture;

final class Fixture
{
    protected $protectedProperty;
    public $publicProperty;
    protected static $protectedStaticProperty;
    private $privateProperty;
    public static $publicStaticProperty;
    private static $privateStaticProperty;
}

?>
-----
<?php

namespace Rector\Tests\Order\Rector\Class_\OrderPropertiesByVisibilityRector\Fixture;

final class Fixture
{
    public $publicProperty;
    public static $publicStaticProperty;
    protected $protectedProperty;
    protected static $protectedStaticProperty;
    private $privateProperty;
    private static $privateStaticProperty;
}

?>
