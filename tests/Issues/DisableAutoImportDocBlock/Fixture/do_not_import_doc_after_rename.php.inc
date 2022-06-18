<?php

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/someUrl")
 */
class SomeController extends Controller
{
}

?>
-----
<?php

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @\Symfony\Component\Routing\Annotation\Route("/someUrl")
 */
class SomeController extends Controller
{
}

?>
