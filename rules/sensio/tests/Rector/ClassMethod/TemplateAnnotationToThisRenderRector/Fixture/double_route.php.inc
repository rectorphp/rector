<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/hey", name="hey")
 * @Route("/hey-me", name="hey_me")
 */
class DoubleRoute extends AbstractController
{
    /**
     * @Route("/property", name="property")
     * @Route("/property-me", name="property_me")
     */
    private $value;

    /**
     * @Route("/change", name="facility_change")
     * @Route("/change-me", name="facility_change_me")
     */
    public function index()
    {
        return [];
    }
}
