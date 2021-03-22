<?php

declare(strict_types=1);

namespace Doctrine\ORM;

use Doctrine\Common\Persistence\ObjectManager;

if (interface_exists('Doctrine\ORM\EntityManagerInterface')) {
    return;
}

interface EntityManagerInterface extends ObjectManager
{

}
