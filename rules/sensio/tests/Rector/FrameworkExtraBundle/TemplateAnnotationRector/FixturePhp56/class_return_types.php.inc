<?php

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\FixturePhp56;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClassReturnTypeController extends AbstractController
{
    /**
     * @Template("AppBundle:Module:index.html.twig")
     *
     * @return array
     */
    public function indexAction()
    {
        return [];
    }
}

?>
-----
<?php

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\FixturePhp56;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClassReturnTypeController extends AbstractController
{
    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('AppBundle:Module:index.html.twig');
    }
}

?>
