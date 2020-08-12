<?php

declare(strict_types=1);

namespace Doctrine\Bundle\DoctrineBundle\Repository;

if (class_exists('Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository')) {
    return;
}

abstract class ServiceEntityRepository
{
}
