<?php

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\ClassMethod\FixtureDifferentBundleName\SomeActionBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BasicController extends AbstractController
{
    /**
     * @Template()
     */
    public function someAction()
    {
        return [];
    }
}

?>
-----
<?php

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\ClassMethod\FixtureDifferentBundleName\SomeActionBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BasicController extends AbstractController
{
    public function someAction(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('@DifferentNameBundle/Basic/some.html.twig');
    }
}

?>
