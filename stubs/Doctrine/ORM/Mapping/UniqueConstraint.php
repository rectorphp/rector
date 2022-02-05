<?php

declare(strict_types=1);

namespace Doctrine\ORM\Mapping;

if (class_exists('Doctrine\ORM\Mapping\UniqueConstraint')) {
    return;
}

final class UniqueConstraint
{
    public function __construct($name, $columns = [])
    {
    }
}
