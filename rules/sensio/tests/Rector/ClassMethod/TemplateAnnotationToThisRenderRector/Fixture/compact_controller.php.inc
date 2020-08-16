<?php

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\ClassMethod\Fixture;

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

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\ClassMethod\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CompactController extends AbstractController
{
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        $hello = 'hi';
        $world = 'earth';

        return $this->render('@FrameworkExtra/index.html.twig', ['hello' => $hello, 'world' => $world]);
    }
}

?>
