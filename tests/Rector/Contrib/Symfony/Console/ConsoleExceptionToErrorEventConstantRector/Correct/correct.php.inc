<?php declare(strict_types=1);

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