<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class WithRouteOptions extends AbstractController
{
    /**
     * @Route("/{category}", name="report_overview", defaults={"category"=null}, requirements={"category"="[a-z]\d+"})
     * @Template("PAPPReportBundle:Report:report_list.html.twig")
     */
    public function index($category = null)
    {
        return [
            'category' => $category,
        ];
    }
}

?>
-----
<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class WithRouteOptions extends AbstractController
{
    /**
     * @Route("/{category}", name="report_overview", defaults={"category"=null}, requirements={"category"="[a-z]\d+"})
     */
    public function index($category = null): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('PAPPReportBundle:Report:report_list.html.twig', [
            'category' => $category,
        ]);
    }
}

?>
