<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class ClassWithArrayAndManyResponseController extends AbstractController
{
    /**
     * @Template()
     */
    public function indexAction()
    {
        return $this->handleStuff();
    }

    /**
     * @return string[]|Response
     */
    private function handleStuff()
    {
        if (mt_rand(0, 100)) {
            return [
                'hello' => 'world'
            ];
        } else {
            return new JsonResponse([]);
        }

        return new RedirectResponse('elsewhere');
    }
}

?>
-----
<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class ClassWithArrayAndManyResponseController extends AbstractController
{
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        $responseOrData = $this->handleStuff();
        if ($responseOrData instanceof \Symfony\Component\HttpFoundation\Response) {
            return $responseOrData;
        }
        return $this->render('@App/Class_With_Array_And_Many_Response/index.html.twig', $responseOrData);
    }

    /**
     * @return string[]|Response
     */
    private function handleStuff()
    {
        if (mt_rand(0, 100)) {
            return [
                'hello' => 'world'
            ];
        } else {
            return new JsonResponse([]);
        }

        return new RedirectResponse('elsewhere');
    }
}

?>
