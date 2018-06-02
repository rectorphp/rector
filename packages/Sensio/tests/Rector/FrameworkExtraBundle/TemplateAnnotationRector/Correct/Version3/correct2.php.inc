<?php declare (strict_types=1);

namespace Rector\AppBundle\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class ClassWithNamedService1Controller extends SymfonyController
{
    public function indexAction()
    {
        return $this->render('AppBundle:ClassWithNamedService1:index.html.twig', ['someKey' => 'someValue']);
    }
}
