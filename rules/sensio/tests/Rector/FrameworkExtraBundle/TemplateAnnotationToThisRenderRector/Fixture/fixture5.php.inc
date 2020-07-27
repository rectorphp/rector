<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClassWithNamedService55Controller extends AbstractController
{
    /**
     * @Template()
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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClassWithNamedService55Controller extends AbstractController
{
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('Class_With_Named_Service55/index.html.twig');
    }
}

?>
