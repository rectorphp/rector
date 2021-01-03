<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ReturnResponseController extends AbstractController
{
    /**
     * @Template()
     */
    public function indexAction()
    {
        if ($response = $this->createResponse()) {
            return $response;
        }

        return new RedirectResponse('...');
    }

    /**
     * @return RedirectResponse|null
     */
    private function createResponse()
    {
        if (mt_rand(0, 100)) {
            return new RedirectResponse('...');
        }

        return null;
    }
}

?>
-----
<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ReturnResponseController extends AbstractController
{
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        if ($response = $this->createResponse()) {
            return $response;
        }

        return new RedirectResponse('...');
    }

    /**
     * @return RedirectResponse|null
     */
    private function createResponse()
    {
        if (mt_rand(0, 100)) {
            return new RedirectResponse('...');
        }

        return null;
    }
}

?>
