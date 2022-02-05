<?php

declare(strict_types=1);

namespace Doctrine\ORM\Mapping;

if (class_exists('Doctrine\ORM\Mapping\Table')) {
    return;
}

final class Table
{
    public function __construct($name, $uniqueConstraints = [])
    {
    }
}
