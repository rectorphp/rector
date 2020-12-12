<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class TestController extends AbstractController
{
    public function indexAction()
    {
        $this->getDoctrine()->getManager()->getRepository('App:Post');
    }
}

?>
-----
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class TestController extends AbstractController
{
    public function indexAction()
    {
        $this->getDoctrine()->getManager()->getRepository(\App\Entity\Post::class);
    }
}

?>
