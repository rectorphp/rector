<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class WithOnlyVarsController extends AbstractController
{
    /**
     * @Template(vars={"post"})
     */
    public function index(Post $post)
    {
    }
}

?>
-----
<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class WithOnlyVarsController extends AbstractController
{
    public function index(Post $post): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('With_Only_Vars/index.html.twig', ['post' => $post]);
    }
}

?>
