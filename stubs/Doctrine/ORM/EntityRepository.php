<?php

declare(strict_types=1);

namespace Doctrine\ORM;

if (class_exists('Doctrine\ORM\EntityRepository')) {
    return;
}

// @see https://github.com/doctrine/orm/blob/2.8.x/lib/Doctrine/ORM/EntityRepository.php
class EntityRepository
{
    /**
     * @var EntityManager
     */
    protected $_em;
}
