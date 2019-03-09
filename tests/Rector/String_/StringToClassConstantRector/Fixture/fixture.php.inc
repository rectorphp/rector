<?php

namespace Rector\Tests\Rector\String_\StringToClassConstantRector\Fixture;

final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'compiler.post_dump' => 'compile',
            'compiler.to_class' => 'compile'
        ];
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
        return [
            \Yet\AnotherClass::CONSTANT => 'compile',
            \Yet\AnotherClass::class => 'compile'
        ];
    }
}

?>
