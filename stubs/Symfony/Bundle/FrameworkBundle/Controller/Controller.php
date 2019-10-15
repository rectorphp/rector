<?php

declare(strict_types=1);

namespace Symfony\Bundle\FrameworkBundle\Controller;

use Symfony\Component\Form\FormInterface;

if (class_exists('Symfony\Bundle\FrameworkBundle\Controller\Controller')) {
    return;
}

class Controller
{
    public function createForm(): FormInterface
    {
    }
}
