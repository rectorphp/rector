<?php declare (strict_types=1);

namespace App\Controller;

class AppController extends AbstractController
{
    /**
     * @Route ("/", name="homepage")
     */
    public function index()
    {
        return $this->render('app/index.html.twig');
    }
}
