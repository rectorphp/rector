<?php

namespace Rector\Generic\Tests\Rector\String_\StringToClassConstantRector\Fixture;

final class Fixture
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

namespace Rector\Generic\Tests\Rector\String_\StringToClassConstantRector\Fixture;

final class Fixture
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
