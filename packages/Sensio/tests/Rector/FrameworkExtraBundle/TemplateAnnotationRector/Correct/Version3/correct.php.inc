<?php

namespace AppBundle\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class ClassWithNamedService13Controller extends SymfonyController
{
    /**
     * @Template
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Template
     */
    public function index2Action()
    {
        return ['someKey' => 'someValue'];
    }

    /**
     * @Template("someFile.toBe.used")
     */
    public function index3Action()
    {
        return [];
    }
}

?>
-----
<?php

namespace AppBundle\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class ClassWithNamedService13Controller extends SymfonyController
{
    public function indexAction()
    {
        return $this->render('AppBundle:ClassWithNamedService13:index.html.twig');
    }

    public function index2Action()
    {
        return $this->render('AppBundle:ClassWithNamedService13:index2.html.twig', ['someKey' => 'someValue']);
    }

    public function index3Action()
    {
        return $this->render('someFile.toBe.used');
    }
}

?>
