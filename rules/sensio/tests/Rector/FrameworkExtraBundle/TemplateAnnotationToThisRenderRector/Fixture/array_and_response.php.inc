<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class ClassWithArrayAndResponseController extends AbstractController
{
    /**
     * @Template("with_some_template.twig")
     */
    public function indexAction()
    {
        return $this->handleStuff();
    }

    /**
     * @return array|RedirectResponse
     */
    private function handleStuff()
    {
        if (mt_rand(0, 100)) {
            return [
                'hello' => 'world'
            ];
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
use Symfony\Component\HttpFoundation\RedirectResponse;

final class ClassWithArrayAndResponseController extends AbstractController
{
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        $responseOrData = $this->handleStuff();
        if ($responseOrData instanceof \Symfony\Component\HttpFoundation\Response) {
            return $responseOrData;
        }
        return $this->render('with_some_template.twig', $responseOrData);
    }

    /**
     * @return array|RedirectResponse
     */
    private function handleStuff()
    {
        if (mt_rand(0, 100)) {
            return [
                'hello' => 'world'
            ];
        }

        return new RedirectResponse('elsewhere');
    }
}

?>
