<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;

class ClassTryCatchArrayResponseController extends AbstractController
{
    /**
     * @Template()
     */
    public function indexAction()
    {
        try {
            return [
                'key' => 'value'
            ];
        } catch (Throwable $throwable) {
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

class ClassTryCatchArrayResponseController extends AbstractController
{
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        try {
            return $this->render('@App/Class_Try_Catch_Array_Response/index.html.twig', [
                'key' => 'value'
            ]);
        } catch (Throwable $throwable) {
            return new RedirectResponse('here');
        }
    }
}

?>
