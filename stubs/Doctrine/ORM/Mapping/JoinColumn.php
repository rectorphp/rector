<?php

declare(strict_types=1);

namespace Doctrine\ORM\Mapping;

if (class_exists('Doctrine\ORM\Mapping\JoinColumn')) {
    return;
}

final class JoinColumn
{
    public function __construct($name, $referencedColumnName = null)
    {
    }
}
