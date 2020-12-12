<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ClassWithReturnTypes extends AbstractController
{
    /**
     * @Template("AppBundle:Module:index.html.twig")
     *
     * @return array
     */
    public function indexAction(): array
    {
        return [];
    }

    /**
     * @Template("AppBundle:Module:index2.html.twig")
     *
     * @return array|Response
     */
    public function index2Action()
    {
        if (true) {
            return $this->redirectToRoute('index');
        }

        return [];
    }

    /**
     * @Template("AppBundle:Module:index3.html.twig")
     *
     * @return Response
     */
    public function index3Action(): Response
    {
        return $this->render('AppBundle:Module:index3.html.twig');
    }
}

?>
-----
<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ClassWithReturnTypes extends AbstractController
{
    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('AppBundle:Module:index.html.twig');
    }

    /**
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index2Action(): \Symfony\Component\HttpFoundation\Response
    {
        if (true) {
            return $this->redirectToRoute('index');
        }

        return $this->render('AppBundle:Module:index2.html.twig');
    }

    /**
     *
     * @return Response
     */
    public function index3Action(): Response
    {
        return $this->render('AppBundle:Module:index3.html.twig');
    }
}

?>
