<?php

declare(strict_types=1);

namespace Doctrine\Persistence;

use Doctrine\ORM\EntityManagerInterface;

if (class_exists('Doctrine\Persistence\ManagerRegistry')) {
    return;
}

final class ManagerRegistry
{
    public function getManager(): ObjectManager
    {
    }
}
