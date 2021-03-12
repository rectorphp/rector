<?php

namespace Rector\Symfony3\Tests\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector\Fixture;

final class Fixture
{
    public static function subscribeTo()
    {
        return [
            'console.exception' => someFunction(),
            \Symfony\Component\Console\ConsoleEvents::EXCEPTION => someFunction(),
        ];
    }
}

?>
-----
<?php

namespace Rector\Symfony3\Tests\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector\Fixture;

final class Fixture
{
    public static function subscribeTo()
    {
        return [
            \Symfony\Component\Console\ConsoleEvents::ERROR => someFunction(),
            \Symfony\Component\Console\ConsoleEvents::ERROR => someFunction(),
        ];
    }
}

?>
