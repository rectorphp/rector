<?php declare(strict_types=1);

final class SomeSubscriber
{
    public static function subscribeTo()
    {
        return [
            'console.exception' => someFunction(),
            Symfony\Component\Console\ConsoleEvents::EXCEPTION => someFunction(),
        ];
    }    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }}