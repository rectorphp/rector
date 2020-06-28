<?php

declare(strict_types=1);

if (class_exists('Mockery')) {
    return;
}

class Mockery
{
    /**
     * @param mixed ...$args
     *
     * @return \Mockery\MockInterface
     */
    public static function mock(...$args) {
        return new \Mockery\DummyMock();
    }
}
