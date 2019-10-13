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
        return '4398dda9-3bc0-45ec-ae81-3d81a86ad84a';
    }
}
