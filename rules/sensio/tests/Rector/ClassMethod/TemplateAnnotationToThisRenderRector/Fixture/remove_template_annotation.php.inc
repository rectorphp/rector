<?php

namespace Rector\Sensio\Tests\Rector\ClassMethod\TemplateAnnotationToThisRenderRector\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class RemoveTemplateAnnotatoinController extends AbstractController
{
    /**
     * @Template()
     */
    public function indexAction()
    {
        return new Response('...');
    }
}

?>
-----
<?php

namespace Rector\Sensio\Tests\Rector\ClassMethod\TemplateAnnotationToThisRenderRector\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class RemoveTemplateAnnotatoinController extends AbstractController
{
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        return new Response('...');
    }
}

?>
