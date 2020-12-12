<?php

namespace Rector\Order\Tests\Rector\Class_\OrderPropertiesByVisibilityRector\Fixture;

abstract class AbstractClass
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

namespace Rector\Order\Tests\Rector\Class_\OrderPropertiesByVisibilityRector\Fixture;

abstract class AbstractClass
{
    public $publicProperty;
    public static $publicStaticProperty;
    protected $protectedProperty;
    protected static $protectedStaticProperty;
    private $privateProperty;
    private static $privateStaticProperty;
}

?>
