<?php

declare(strict_types=1);

namespace Doctrine\ORM\Mapping;

if (class_exists('Doctrine\ORM\Mapping\Index')) {
    return;
}

final class Index
{
    public function __construct($name, $columns = [])
    {
    }
}
