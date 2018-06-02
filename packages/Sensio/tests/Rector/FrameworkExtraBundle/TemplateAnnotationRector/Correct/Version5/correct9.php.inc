<?php declare (strict_types=1);

namespace App\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class AppController extends SymfonyController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        return $this->render('app/index.html.twig');
    }
}
