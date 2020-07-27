<?php

declare(strict_types=1);

namespace Ramsey\Uuid;

if (class_exists('Ramsey\Uuid\Uuid')) {
    return;
}

class Uuid implements UuidInterface
{
    public static function uuid4(): self
    {
        return new Uuid();
    }

    public function toString()
    {
        // dummy value
        return '%s-%s-%s-%s-%s';
    }
}
