<?php declare (strict_types=1);

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class ClassWithArrayAndResponseWithArrayController extends AbstractController
{
    /**
     * @Template("with_some_template.twig")
     */
    public function indexAction()
    {
        return $this->handleStuff([
            'a' => 'b'
        ]);
    }

    /**
     * @return array|RedirectResponse
     */
    private function handleStuff(...$args)
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

final class ClassWithArrayAndResponseWithArrayController extends AbstractController
{
    public function indexAction(): \Symfony\Component\HttpFoundation\Response
    {
        $response = $this->handleStuff([
            'a' => 'b'
        ]);
        if ($response instanceof \Symfony\Component\HttpFoundation\Response) {
            return $response;
        }
        return $this->render('with_some_template.twig');
    }

    /**
     * @return array|RedirectResponse
     */
    private function handleStuff(...$args)
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
