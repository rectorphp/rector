<?php

declare(strict_types=1);

namespace Symfony\Bundle\FrameworkBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;

if (class_exists('Symfony\Bundle\FrameworkBundle\Controller\AbstractController')) {
    return;
}

abstract class AbstractController
{
    public function getDoctrine(): ManagerRegistry
    {
    }
}
