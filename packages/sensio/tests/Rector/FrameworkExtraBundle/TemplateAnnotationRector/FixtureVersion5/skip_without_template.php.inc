<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class WithoutBaseClassAndTemplate
{
    /**
     * @Route("/route", name="route")
     */
    public function index(): array
    {
        return [];
    }
}
