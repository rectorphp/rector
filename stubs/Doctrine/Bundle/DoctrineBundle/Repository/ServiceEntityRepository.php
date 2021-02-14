<?php

declare(strict_types=1);

namespace Doctrine\Bundle\DoctrineBundle\Repository;


use Doctrine\ORM\EntityRepository;

if (class_exists('Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository')) {
    return;
}

// @see https://github.com/doctrine/DoctrineBundle/blob/2.2.x/Repository/ServiceEntityRepository.php
abstract class ServiceEntityRepository extends EntityRepository
{
}
