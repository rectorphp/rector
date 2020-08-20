<?php

namespace Rector\Sensio\Tests\Rector\ClassMethod\TemplateAnnotationToThisRenderRector\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CompactController extends AbstractController
{
    /**
     * @Template()
     */
    public function index()
    {
        $hello = 'hi';
        $world = 'earth';

        return compact('hello', 'world');
    }
}

?>
-----
<?php

namespace Rector\Sensio\Tests\Rector\ClassMethod\TemplateAnnotationToThisRenderRector\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CompactController extends AbstractController
{
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        $hello = 'hi';
        $world = 'earth';

        return $this->render('index.html.twig', ['hello' => $hello, 'world' => $world]);
    }
}

?>
