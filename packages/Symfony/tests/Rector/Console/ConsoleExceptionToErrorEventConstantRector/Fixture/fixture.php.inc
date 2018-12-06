<?php

namespace Rector\Symfony\Tests\Rector\Console\ConsoleExceptionToErrorEventConstantRector\Wrong;

final class SomeSubscriber
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

namespace Rector\Symfony\Tests\Rector\Console\ConsoleExceptionToErrorEventConstantRector\Wrong;

final class SomeSubscriber
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
