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
    public function index()
    {
        return $this->render('class_with_named_service55/index.html.twig');
    }
}

?>
