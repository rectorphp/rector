<?php

namespace Rector\Symfony\Tests\Symfony26\Rector\MethodCall\RedirectToRouteRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class GenerateUrlWithDefault extends AbstractController
{
    public function someAction()
    {
        return $this->redirect(
            $this->generateUrl('something', [], UrlGeneratorInterface::ABSOLUTE_PATH)
        );
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Symfony26\Rector\MethodCall\RedirectToRouteRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class GenerateUrlWithDefault extends AbstractController
{
    public function someAction()
    {
        return $this->redirectToRoute('something', []);
    }
}

?>
