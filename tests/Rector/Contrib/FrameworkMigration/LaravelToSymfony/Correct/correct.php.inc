<?php declare(strict_types=1);

namespace App\Controller;

use \Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;

class UserController extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    public function show(): \Symfony\Component\HttpFoundation\Response
    {
        return new Response('user.profile');
    }
}
