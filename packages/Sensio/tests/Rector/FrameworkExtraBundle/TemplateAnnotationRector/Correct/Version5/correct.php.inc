<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class ClassWithNamedService1Controller extends SymfonyController
{
    public function indexAction()
    {
        return $this->render('@App/class_with_named_service1/index.html.twig');
    }
}
