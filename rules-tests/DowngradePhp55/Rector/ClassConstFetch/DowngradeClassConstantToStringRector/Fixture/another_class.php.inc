<?php

namespace Rector\Tests\DowngradePhp55\Rector\ClassConstFetch\DowngradeClassConstantToStringRector\Fixture;

abstract class AnotherClass
{
    public function run()
    {
        return self::class . '/' . static::class;
    }
}

final class ChildClass extends AnotherClass
{
    public function run()
    {
        return parent::Class;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp55\Rector\ClassConstFetch\DowngradeClassConstantToStringRector\Fixture;

abstract class AnotherClass
{
    public function run()
    {
        return get_class() . '/' . get_called_class();
    }
}

final class ChildClass extends AnotherClass
{
    public function run()
    {
        return get_parent_class();
    }
}

?>
