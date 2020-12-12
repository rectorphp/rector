<?php

namespace Rector\Symfony3\Tests\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class ClassWithTemplateAnnotation2Controller
{
    /**
     * @Route("/show/{id}")
     * @Template("AdminBundle:Payment:create.html.twig")
     * @Method({"GET", "HEAD"})
     */
    public function show($id)
    {
    }
}

?>
-----
<?php

namespace Rector\Symfony3\Tests\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class ClassWithTemplateAnnotation2Controller
{
    /**
     * @Route("/show/{id}", methods={"GET", "HEAD"})
     * @Template("AdminBundle:Payment:create.html.twig")
     */
    public function show($id)
    {
    }
}

?>
