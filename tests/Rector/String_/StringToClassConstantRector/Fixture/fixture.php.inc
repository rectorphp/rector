<?php

namespace Rector\Tests\Rector\String_\StringToClassConstantRector\Fixture;

final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return ['compiler.post_dump' => 'compile'];
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\String_\StringToClassConstantRector\Fixture;

final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return [\Yet\AnotherClass::CONSTANT => 'compile'];
    }
}

?>
