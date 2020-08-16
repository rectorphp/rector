<?php

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\ClassMethod\FixturePhp56;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class ClassReturnTypeController extends AbstractController
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

    /**
     * @Template("AppBundle:AnotherModule:index.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function listAction()
    {
        return [];
    }
}

?>
-----
<?php

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\ClassMethod\FixturePhp56;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class ClassReturnTypeController extends AbstractController
{
    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('AppBundle:Module:index.html.twig');
    }

    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        return $this->render('AppBundle:AnotherModule:index.html.twig');
    }
}

?>
