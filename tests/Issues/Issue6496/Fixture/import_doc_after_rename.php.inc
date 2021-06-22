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

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/someUrl")
 */
class SomeController extends Controller
{
}

?>
