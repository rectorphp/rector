<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmptyBodyController extends AbstractController
{
    /**
     * @Template
     */
    public function indexAction()
    {
    }
}

?>
-----
<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EmptyBodyController extends AbstractController
{
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('@App/empty_body/index.html.twig');
    }
}

?>
