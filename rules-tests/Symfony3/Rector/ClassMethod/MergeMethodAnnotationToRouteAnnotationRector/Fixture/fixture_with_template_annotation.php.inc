<?php

namespace Rector\Tests\Symfony3\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class ClassWithTemplateAnnotationController
{
    /**
     * @Route("/show/{id}")
     * @Method({"GET", "HEAD"})
     * @Template("AdminBundle:Payment:create.html.twig")
     */
    public function show($id)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Symfony3\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class ClassWithTemplateAnnotationController
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
