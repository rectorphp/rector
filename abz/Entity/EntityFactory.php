<?php

declare(strict_types=1);

namespace App\Entity;

final class EntityFactory
{
    private SomeInterface $property;

    public function create(SomeInterface $param): SomeInterface
    {
    }
}
