<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class WithRouteOptions
{
    /**
     * @Route("/{category}", name="report_overview", defaults={"category":null}, requirements={"category":"\d+"})
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
use Symfony\Component\Routing\Annotation\Route;

class WithRouteOptions
{
    /**
     * @Route("/{category}", name="report_overview", defaults={"category":null}, requirements={"category":"\d+"})
     */
    public function index($category = null)
    {
        return $this->render('PAPPReportBundle:Report:report_list.html.twig', [
            'category' => $category,
        ]);
    }
}

?>
