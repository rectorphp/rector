<?php

declare(strict_types=1);

namespace Doctrine\Common\Persistence;

use Doctrine\ORM\EntityManagerInterface;

if (class_exists('Doctrine\Common\Persistence\ManagerRegistry')) {
    return;
}

final class ManagerRegistry
{
    public function getManager(): EntityManagerInterface
    {
    }
}
