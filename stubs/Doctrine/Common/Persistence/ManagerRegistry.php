<?php

declare(strict_types=1);

namespace Doctrine\Common\Persistence;

if (class_exists('Doctrine\Common\Persistence\ManagerRegistry')) {
    return;
}

final class ManagerRegistry
{
    /**
     * @return ObjectManager
     */
    public function getManager()
    {
    }
}
