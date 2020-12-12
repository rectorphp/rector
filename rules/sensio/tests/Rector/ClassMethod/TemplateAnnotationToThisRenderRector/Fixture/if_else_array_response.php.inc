<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

class ClassIfElseArrayController extends AbstractController
{
    /**
     * @Template()
     */
    public function indexAction()
    {
        if (mt_rand(0, 100)) {
            return [
                'key' => 'value'
            ];
        } elseif (mt_rand(0, 200)) {
            return [
                'key' => 'value2'
            ];
        } else {
            return new RedirectResponse('here');
        }
    }
}

?>
-----
<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

class ClassIfElseArrayController extends AbstractController
{
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        if (mt_rand(0, 100)) {
            return $this->render('@App/Class_If_Else_Array/index.html.twig', [
                'key' => 'value'
            ]);
        } elseif (mt_rand(0, 200)) {
            return $this->render('@App/Class_If_Else_Array/index.html.twig', [
                'key' => 'value2'
            ]);
        } else {
            return new RedirectResponse('here');
        }
    }
}

?>
