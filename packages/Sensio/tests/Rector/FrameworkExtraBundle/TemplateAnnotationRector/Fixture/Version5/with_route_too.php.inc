<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class WithRouteToo
{
    /**
     * @Route("/change", name="facility_change")
     * @Template("PAPPUserBundle:Facility:facility.html.twig")
     */
    public function index()
    {
        return [];
    }
}

?>
-----
<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class WithRouteToo
{
    /**
     * @Route("/change", name="facility_change")
     */
    public function index()
    {
        return $this->render('PAPPUserBundle:Facility:facility.html.twig');
    }
}

?>
